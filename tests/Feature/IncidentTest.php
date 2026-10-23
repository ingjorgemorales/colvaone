<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\IncidentOption;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IncidentTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $permissions): User
    {
        $role = Role::create(['name' => 'Prueba '.Role::count(), 'slug' => 'prueba-'.Role::count(), 'permissions' => $permissions, 'is_active' => true]);

        return User::factory()->create(['role' => $role->slug]);
    }

    private function data(): array
    {
        $data = ['reporter_name' => 'Persona que reporta', 'reporter_position' => 'Analista', 'location' => 'Oficina norte', 'description' => 'Falla del equipo'];
        foreach (['area', 'failure', 'impact', 'priority'] as $type) {
            $data[$type.'_id'] = IncidentOption::firstOrCreate(['type' => $type, 'name' => 'Opción '.$type], ['is_active' => true])->id;
        }

        return $data;
    }

    private function incident(User $user, array $extra = []): Incident
    {
        $incident = Incident::create(array_merge($this->data(), ['created_by' => $user->id], $extra));
        if (isset($extra['created_at'])) {
            $incident->forceFill(['created_at' => $extra['created_at']])->save();
        }

        return $incident;
    }

    public function test_own_visibility_and_direct_urls_are_scoped_to_creator(): void
    {
        $owner = $this->userWith(['incidents.view', 'incidents.edit', 'incidents.toggle', 'incidents.archive']);
        $mine = $this->incident($owner);
        $foreign = $this->incident(User::factory()->create(), ['status' => 'resuelto']);
        $this->actingAs($owner)->get(route('incidents.index'))->assertOk()->assertSee($mine->ticket)->assertDontSee($foreign->ticket);
        $this->get(route('incidents.show', $foreign))->assertForbidden();
        $this->get(route('incidents.edit', $foreign))->assertForbidden();
        $this->put(route('incidents.update', $foreign), $this->data() + ['status' => 'en_atencion'])->assertForbidden();
        $this->post(route('incidents.toggle', $foreign))->assertForbidden();
        $this->post(route('incidents.archive', $foreign))->assertForbidden();
    }

    public function test_all_visibility_wins_and_inactive_roles_have_no_access(): void
    {
        $reader = $this->userWith(['incidents.view', 'incidents.view_all']);
        $foreign = $this->incident(User::factory()->create());
        $this->actingAs($reader)->get(route('incidents.index'))->assertOk()->assertSee($foreign->ticket);
        $this->get(route('incidents.show', $foreign))->assertOk();
        $reader->roleObject->update(['is_active' => false]);
        $this->get(route('incidents.index'))->assertForbidden();
        $this->get(route('incidents.show', $foreign))->assertForbidden();
    }

    public function test_action_permissions_require_visibility_and_readers_cannot_write(): void
    {
        $user = $this->userWith(['incidents.create', 'incidents.edit', 'incidents.toggle', 'incidents.archive']);
        $incident = $this->incident($user);
        $this->actingAs($user)->get(route('incidents.index'))->assertForbidden();
        $this->get(route('incidents.create'))->assertForbidden();
        $this->post(route('incidents.store'), $this->data())->assertForbidden();
        $this->actingAs($this->userWith(['incidents.view_all']));
        $this->get(route('incidents.create'))->assertForbidden();
        $this->post(route('incidents.store'), $this->data())->assertForbidden();
        $this->get(route('incidents.edit', $incident))->assertForbidden();
        $this->put(route('incidents.update', $incident), $this->data())->assertForbidden();
        $this->post(route('incidents.toggle', $incident))->assertForbidden();
        $this->post(route('incidents.archive', $incident))->assertForbidden();
        $this->get(route('incidents.settings'))->assertForbidden();
        $this->post(route('incidents.options.store'), [])->assertForbidden();
    }

    public function test_creation_validation_ownership_and_private_evidence(): void
    {
        Storage::fake('local');
        $user = $this->userWith(['incidents.view', 'incidents.create']);
        $this->actingAs($user)->post(route('incidents.store'), [])->assertSessionHasErrors(['reporter_name', 'area_id', 'description']);
        $this->post(route('incidents.store'), $this->data() + [
            'created_by' => 999, 'status' => 'resuelto', 'is_active' => false,
            'attachments' => [UploadedFile::fake()->create('evidencia.pdf', 20, 'application/pdf')],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $incident = Incident::firstOrFail();
        $this->assertSame($user->id, $incident->created_by);
        $this->assertSame('reportado', $incident->status);
        $this->assertTrue($incident->is_active);
        $attachment = $incident->attachments()->firstOrFail();
        Storage::disk('local')->assertExists($attachment->path);
        $this->get(route('incidents.attachments.download', [$incident, $attachment]))->assertDownload('evidencia.pdf');
        $this->actingAs($this->userWith(['incidents.view']))->get(route('incidents.attachments.download', [$incident, $attachment]))->assertForbidden();
        $other = $this->incident($user);
        $this->actingAs($this->userWith(['incidents.view_all']))->get(route('incidents.attachments.download', [$other, $attachment]))->assertNotFound();
    }

    public function test_options_validate_type_activity_and_preserve_existing_inactive_values(): void
    {
        $user = $this->userWith(['incidents.view', 'incidents.create', 'incidents.edit']);
        $data = $this->data();
        $incident = $this->incident($user);
        IncidentOption::findOrFail($data['failure_id'])->update(['is_active' => false]);
        $this->actingAs($user)->put(route('incidents.update', $incident), $data + ['status' => 'en_atencion'])->assertSessionHasNoErrors();
        $this->assertSame('en_atencion', $incident->fresh()->status);
        $this->post(route('incidents.store'), $data)->assertSessionHasErrors('failure_id');
        $data['failure_id'] = $data['area_id'];
        $this->post(route('incidents.store'), $data)->assertSessionHasErrors('failure_id');
    }

    public function test_archive_restore_toggle_and_filters(): void
    {
        $user = $this->userWith(['incidents.view', 'incidents.archive', 'incidents.toggle', 'incidents.edit']);
        $incident = $this->incident($user, ['created_at' => '2026-10-10 12:00:00']);
        $this->actingAs($user)->post(route('incidents.archive', $incident))->assertStatus(422);
        $incident->update(['status' => 'resuelto']);
        $this->post(route('incidents.toggle', $incident))->assertRedirect();
        $this->assertFalse($incident->fresh()->is_active);
        $this->post(route('incidents.archive', $incident))->assertRedirect();
        $this->get(route('incidents.index'))->assertDontSee($incident->ticket);
        $this->get(route('incidents.index', ['archive' => 'archived']))->assertSee($incident->ticket);
        $this->get(route('incidents.edit', $incident))->assertForbidden();
        $this->put(route('incidents.update', $incident), $this->data())->assertForbidden();
        $this->post(route('incidents.toggle', $incident))->assertForbidden();
        $this->post(route('incidents.archive', $incident))->assertRedirect();
        $this->assertNull($incident->fresh()->archived_at);
        $this->get(route('incidents.index', ['search' => $incident->ticket, 'status' => 'resuelto', 'activity' => 'inactive', 'from' => '2026-10-01', 'to' => '2026-10-20']))->assertOk()->assertViewHas('incidents', fn ($rows) => $rows->modelKeys() === [$incident->id]);
        $this->get(route('incidents.index', ['to' => '2026-10-09']))->assertOk()->assertDontSee($incident->ticket);
        $this->get(route('incidents.index', ['activity' => 'active']))->assertDontSee($incident->ticket);
        $this->get(route('incidents.index', ['priority_id' => 9999]))->assertDontSee($incident->ticket);
    }

    public function test_catalogs_create_rename_reorder_and_deactivate_without_losing_links(): void
    {
        $admin = $this->userWith(['incidents.settings', 'incidents.view_all']);
        $incident = $this->incident($admin);
        $this->actingAs($admin)->post(route('incidents.options.store'), ['type' => 'failure', 'name' => 'Nueva falla', 'sort_order' => 40])->assertSessionHasNoErrors();
        $this->put(route('incidents.options.update', $incident->area_id), ['name' => 'Área actualizada', 'sort_order' => 9])->assertSessionHasNoErrors();
        $this->post(route('incidents.options.toggle', $incident->area_id))->assertRedirect();
        $this->get(route('incidents.show', $incident))->assertSee('Área actualizada');
        $this->assertFalse($incident->area()->firstOrFail()->is_active);
        $this->post(route('incidents.options.store'), ['type' => 'invalid', 'name' => 'No', 'sort_order' => 0])->assertSessionHasErrors('type');
    }

    public function test_attachment_validation_removal_and_count_limits(): void
    {
        Storage::fake('local');
        $user = $this->userWith(['incidents.view', 'incidents.create', 'incidents.edit']);
        $this->actingAs($user)->post(route('incidents.store'), $this->data() + ['attachments' => [UploadedFile::fake()->create('script.html', 5, 'text/html')]])->assertSessionHasErrors('attachments.0');
        $this->post(route('incidents.store'), $this->data() + ['attachments' => [UploadedFile::fake()->create('grande.pdf', 10241, 'application/pdf')]])->assertSessionHasErrors('attachments.0');
        $this->post(route('incidents.store'), $this->data() + ['attachments' => [UploadedFile::fake()->create('informe.pdf', 10, 'application/pdf')]])->assertSessionHasNoErrors();
        $incident = Incident::firstOrFail();
        $attachment = $incident->attachments()->firstOrFail();
        $this->put(route('incidents.update', $incident), $this->data() + ['status' => 'reportado', 'remove_attachments' => [$attachment->id]])->assertSessionHasNoErrors();
        $this->assertSame(0, $incident->attachments()->count());
        Storage::disk('local')->assertMissing($attachment->path);
        $files = array_map(fn ($i) => UploadedFile::fake()->create('informe'.$i.'.pdf', 1, 'application/pdf'), range(1, 11));
        $this->put(route('incidents.update', $incident), $this->data() + ['status' => 'reportado', 'attachments' => $files])->assertSessionHasErrors('attachments');
        array_pop($files);
        $this->put(route('incidents.update', $incident), $this->data() + ['status' => 'reportado', 'attachments' => $files])->assertSessionHasNoErrors();
        $this->assertSame(10, $incident->attachments()->count());
        $this->put(route('incidents.update', $incident), $this->data() + ['status' => 'reportado', 'attachments' => [UploadedFile::fake()->create('extra.pdf', 1, 'application/pdf')]])->assertSessionHasErrors('attachments');
        $this->assertSame(10, $incident->attachments()->count());
    }

    public function test_pages_render_and_both_role_checkboxes_are_available(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $incident = $this->incident($user);
        $role = Role::create(['name' => 'Operador incidentes', 'slug' => 'operador-incidentes', 'permissions' => []]);
        $this->actingAs($user)->get(route('incidents.index'))->assertOk()->assertSee('Gestión de incidentes');
        $this->get(route('incidents.create'))->assertOk()->assertSee('name="area_id"', false);
        $this->get(route('incidents.edit', $incident))->assertOk();
        $this->get(route('incidents.show', $incident))->assertOk();
        $this->get(route('incidents.settings', ['type' => 'failure']))->assertOk()->assertSee('Tipos de falla');
        $this->get(route('roles.edit', $role))->assertOk()->assertSee('value="incidents.view"', false)->assertSee('value="incidents.view_all"', false);
    }

    public function test_photo_previews_are_inline_private_and_scoped_to_the_incident(): void
    {
        Storage::fake('local');
        $owner = $this->userWith(['incidents.view', 'incidents.create']);
        $this->actingAs($owner)->post(route('incidents.store'), $this->data() + ['attachments' => [UploadedFile::fake()->image('evidencia.png', 640, 480)]])->assertSessionHasNoErrors();
        $incident = Incident::firstOrFail();
        $photo = $incident->attachments()->firstOrFail();
        $preview = route('incidents.attachments.preview', [$incident, $photo]);
        $response = $this->get($preview)->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertStringStartsWith('inline;', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->get(route('incidents.show', $incident))->assertSee('data-incident-photo', false)->assertSee('Generar PDF');
        $this->actingAs($this->userWith(['incidents.view']))->get($preview)->assertForbidden();
        $this->actingAs($this->userWith(['incidents.view_all']))->get($preview)->assertOk();
        $other = $this->incident($owner);
        $this->get(route('incidents.attachments.preview', [$other, $photo]))->assertNotFound();
        Storage::disk('local')->delete($photo->path);
        $this->get($preview)->assertNotFound();
    }

    public function test_pdf_contains_embedded_photos_and_obeys_own_and_all_visibility(): void
    {
        Storage::fake('local');
        $owner = $this->userWith(['incidents.view', 'incidents.create']);
        $this->actingAs($owner)->post(route('incidents.store'), $this->data() + ['attachments' => [
            UploadedFile::fake()->image('evidencia.jpg', 1200, 800),
            UploadedFile::fake()->image('evidencia.png', 400, 400),
            UploadedFile::fake()->image('evidencia.webp', 800, 1200),
        ]])->assertSessionHasNoErrors();
        $incident = Incident::firstOrFail();
        $original = $incident->attachments()->firstOrFail();
        $originalHash = hash('sha256', Storage::disk('local')->get($original->path));
        $url = route('incidents.pdf', $incident);
        $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('/Subtype /Image', $response->getContent());
        $this->assertSame($originalHash, hash('sha256', Storage::disk('local')->get($original->path)));
        $this->assertStringContainsString($incident->ticket.'.pdf', $response->headers->get('Content-Disposition'));
        $this->actingAs($this->userWith(['incidents.view']))->get($url)->assertForbidden();
        $this->actingAs($this->userWith(['incidents.view_all']))->get($url)->assertOk();
        $this->actingAs($this->userWith(['incidents.create']))->get($url)->assertForbidden();
    }

    public function test_pdf_handles_missing_photos_and_documents_without_losing_the_incident(): void
    {
        Storage::fake('local');
        $owner = $this->userWith(['incidents.view', 'incidents.create']);
        $this->actingAs($owner)->post(route('incidents.store'), $this->data() + ['attachments' => [
            UploadedFile::fake()->image('foto.png', 200, 200),
            UploadedFile::fake()->create('documento.pdf', 10, 'application/pdf'),
        ]])->assertSessionHasNoErrors();
        $incident = Incident::firstOrFail();
        $document = $incident->attachments()->where('mime_type', 'application/pdf')->firstOrFail();
        $this->get(route('incidents.attachments.preview', [$incident, $document]))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        Storage::disk('local')->delete($incident->attachments()->where('mime_type', 'image/png')->firstOrFail()->path);
        $response = $this->get(route('incidents.pdf', $incident))->assertOk();
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
