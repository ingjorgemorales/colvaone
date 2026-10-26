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

        return User::factory()->create(['role' => $role->slug, 'position' => 'Analista', 'area' => 'Opción area']);
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
        $this->actingAs($user)->post(route('incidents.store'), [])->assertSessionHasErrors(['failure_id', 'location', 'description']);
        $this->post(route('incidents.store'), $this->data() + [
            'created_by' => 999, 'status' => 'resuelto', 'is_active' => false,
            'attachments' => [UploadedFile::fake()->image('evidencia.png', 20, 20)],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $incident = Incident::firstOrFail();
        $this->assertSame($user->id, $incident->created_by);
        $this->assertSame('reportado', $incident->status);
        $this->assertTrue($incident->is_active);
        $attachment = $incident->attachments()->firstOrFail();
        Storage::disk('local')->assertExists($attachment->path);
        $this->get(route('incidents.attachments.download', [$incident, $attachment]))->assertDownload('evidencia.png');
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
        $this->get(route('incidents.index'))->assertSee($incident->ticket);
        $this->get(route('incidents.index', ['status' => 'archivado']))->assertSee($incident->ticket);
        $this->get(route('incidents.edit', $incident))->assertForbidden();
        $this->put(route('incidents.update', $incident), $this->data())->assertForbidden();
        $this->post(route('incidents.toggle', $incident))->assertForbidden();
        $this->post(route('incidents.archive', $incident))->assertRedirect();
        $this->assertNull($incident->fresh()->archived_at);
        $this->get(route('incidents.index', ['search' => '10/10/2026', 'status' => 'resuelto']))->assertOk()->assertViewHas('incidents', fn ($rows) => $rows->modelKeys() === [$incident->id]);
        $this->get(route('incidents.index', ['search' => '09/10/2026']))->assertOk()->assertViewHas('incidents', fn ($rows) => $rows->isEmpty());
        $this->get(route('incidents.index', ['status' => 'activo']))->assertDontSee($incident->ticket);
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
        $this->post(route('incidents.store'), $this->data() + ['attachments' => [UploadedFile::fake()->image('grande.png')->size(10241)]])->assertSessionHasErrors(['attachments.0' => 'Cada imagen de evidencia debe pesar como máximo 10 MB.']);
        $this->post(route('incidents.store'), $this->data() + ['attachments' => [UploadedFile::fake()->image('evidencia.png')]])->assertSessionHasNoErrors();
        $incident = Incident::firstOrFail();
        $attachment = $incident->attachments()->firstOrFail();
        $this->put(route('incidents.update', $incident), $this->data() + ['status' => 'reportado', 'remove_attachments' => [$attachment->id]])->assertSessionHasNoErrors();
        $this->assertSame(0, $incident->attachments()->count());
        Storage::disk('local')->assertMissing($attachment->path);
        $files = array_map(fn ($i) => UploadedFile::fake()->image('evidencia'.$i.'.jpg', 20, 20), range(1, 11));
        $this->put(route('incidents.update', $incident), $this->data() + ['status' => 'reportado', 'attachments' => $files])->assertSessionHasErrors('attachments');
        array_pop($files);
        $this->put(route('incidents.update', $incident), $this->data() + ['status' => 'reportado', 'attachments' => $files])->assertSessionHasNoErrors();
        $this->assertSame(10, $incident->attachments()->count());
        $this->put(route('incidents.update', $incident), $this->data() + ['status' => 'reportado', 'attachments' => [UploadedFile::fake()->image('extra.jpg', 20, 20)]])->assertSessionHasErrors('attachments');
        $this->assertSame(10, $incident->attachments()->count());
    }

    public function test_pages_render_and_both_role_checkboxes_are_available(): void
    {
        $user = User::factory()->create(['role' => 'superadmin', 'position' => 'Analista', 'area' => 'TI']);
        $incident = $this->incident($user);
        $role = Role::create(['name' => 'Operador incidentes', 'slug' => 'operador-incidentes', 'permissions' => []]);
        $this->actingAs($user)->get(route('incidents.index'))->assertOk()->assertSee('Gestión de incidentes');
        $this->get(route('incidents.create'))->assertOk()->assertSee('id="reporter_area"', false)->assertSee('readonly aria-readonly="true"', false)->assertSee('Adjuntar imágenes')
            ->assertSee('accept=".jpg,.jpeg,.png"', false)->assertSee('Solo imágenes JPG o PNG.')->assertDontSee('WEBP o PDF');
        $this->get(route('incidents.edit', $incident))->assertOk();
        $this->get(route('incidents.show', $incident))->assertOk();
        $this->get(route('incidents.settings', ['type' => 'failure']))->assertOk()->assertSee('Tipos de falla');
        $this->get(route('roles.edit', $role))->assertOk()->assertSee('value="incidents.view"', false)->assertSee('value="incidents.view_all"', false);
    }

    public function test_only_jpg_and_png_evidence_is_accepted_and_invalid_updates_preserve_existing_files(): void
    {
        Storage::fake('local');
        $user = $this->userWith(['incidents.view', 'incidents.create', 'incidents.edit']);
        $message = 'Solo puedes subir imágenes JPG o PNG como evidencia.';
        $png = UploadedFile::fake()->image('foto.png', 20, 20)->get();
        $jpeg = UploadedFile::fake()->image('foto.jpg', 20, 20)->get();
        $temporary = [];
        $upload = function (string $name, string $content) use (&$temporary): UploadedFile {
            $temporary[] = $file = UploadedFile::fake()->createWithContent($name, $content);

            return new UploadedFile($file->getPathname(), $name, null, null, true);
        };
        $this->actingAs($user);
        foreach ([
            UploadedFile::fake()->create('informe.pdf', 1, 'application/pdf'),
            UploadedFile::fake()->image('foto.webp', 20, 20),
            UploadedFile::fake()->image('foto.gif', 20, 20),
            $upload('foto.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>'),
            $upload('falso.jpg', 'Esto no es una imagen'),
            $upload('falso.png', '%PDF-1.4'),
            $upload('imagen.pdf', $png),
        ] as $file) {
            $this->post(route('incidents.store'), $this->data() + ['attachments' => [$file]])
                ->assertSessionHasErrors(['attachments.0' => $message]);
            $this->assertSame(0, Incident::count());
            $this->assertSame([], Storage::disk('local')->allFiles('incidents'));
        }
        $this->post(route('incidents.store'), $this->data() + ['attachments' => [
            $upload('foto.JPG', $jpeg),
            $upload('foto.jpeg', $jpeg),
            $upload('foto.PNG', $png),
        ]])->assertSessionHasNoErrors();
        $incident = Incident::firstOrFail();
        $this->assertSame(3, $incident->attachments()->count());
        $existing = $incident->attachments()->firstOrFail();
        $response = $this->putJson(route('incidents.update', $incident), $this->data() + [
            'status' => 'resuelto', 'remove_attachments' => [$existing->id],
            'attachments' => [UploadedFile::fake()->image('nuevo.webp', 20, 20)],
        ])->assertUnprocessable()->assertJsonValidationErrors('attachments.0');
        $this->assertSame($message, $response->json('errors')['attachments.0'][0]);
        $this->assertSame(3, $incident->attachments()->count());
        $this->assertSame('reportado', $incident->fresh()->status);
        Storage::disk('local')->assertExists($existing->path);
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
            UploadedFile::fake()->image('evidencia.jpeg', 800, 1200),
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
        ]])->assertSessionHasNoErrors();
        $incident = Incident::firstOrFail();
        // Previously stored documents remain readable after restricting new uploads.
        Storage::disk('local')->put('incidents/'.$incident->id.'/documento.pdf', '%PDF-1.4');
        $incident->attachments()->create(['path' => 'incidents/'.$incident->id.'/documento.pdf', 'original_name' => 'documento.pdf', 'mime_type' => 'application/pdf', 'size' => 8]);
        $document = $incident->attachments()->where('mime_type', 'application/pdf')->firstOrFail();
        $this->get(route('incidents.attachments.preview', [$incident, $document]))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        Storage::disk('local')->delete($incident->attachments()->where('mime_type', 'image/png')->firstOrFail()->path);
        $response = $this->get(route('incidents.pdf', $incident))->assertOk();
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_reporter_details_are_taken_from_the_profile_and_cannot_be_forged_or_overwritten(): void
    {
        $owner = $this->userWith(['incidents.view', 'incidents.create', 'incidents.edit']);
        $owner->update(['name' => 'Jorge', 'last_name' => 'Morales', 'position' => 'Analista de soporte', 'area' => 'TI']);
        $data = $this->data();
        $this->actingAs($owner)->post(route('incidents.store'), array_merge($data, [
            'reporter_name' => 'Persona falsa', 'reporter_position' => 'Cargo falso', 'reporter_area' => 'Área falsa',
        ]))->assertSessionHasNoErrors();
        $incident = Incident::firstOrFail();
        $this->assertSame('Jorge Morales', $incident->reporter_name);
        $this->assertSame('Analista de soporte', $incident->reporter_position);
        $this->assertSame('TI', $incident->area->name);
        $this->assertNotSame($data['area_id'], $incident->area_id);
        $editor = $this->userWith(['incidents.view_all', 'incidents.edit']);
        $this->actingAs($editor)->put(route('incidents.update', $incident), array_merge($data, [
            'status' => 'en_atencion', 'reporter_name' => 'Nombre alterado', 'reporter_position' => 'Cargo alterado',
        ]))->assertSessionHasNoErrors();
        $incident->refresh();
        $this->assertSame('Jorge Morales', $incident->reporter_name);
        $this->assertSame('Analista de soporte', $incident->reporter_position);
        $this->assertSame('TI', $incident->area->name);
        $this->assertSame('en_atencion', $incident->status);
        $this->get(route('incidents.edit', $incident))->assertSee('value="Jorge Morales"', false)->assertSee('value="TI"', false);
    }

    public function test_an_incomplete_profile_cannot_be_replaced_with_posted_reporter_details(): void
    {
        $user = $this->userWith(['incidents.view', 'incidents.create']);
        $user->update(['position' => null, 'area' => null]);
        $this->actingAs($user)->get(route('incidents.create'))->assertOk()->assertSee('Actualizar perfil');
        $this->post(route('incidents.store'), $this->data() + ['reporter_area' => 'Área enviada'])
            ->assertSessionHasErrors(['reporter_position', 'reporter_area']);
        $this->assertSame(0, Incident::count());
    }

    public function test_general_search_matches_fields_relations_dates_and_partial_tickets_without_leaking_other_users(): void
    {
        $owner = $this->userWith(['incidents.view']);
        $area = IncidentOption::create(['type' => 'area', 'name' => 'Administración TI']);
        $zone = IncidentOption::create(['type' => 'zone', 'name' => 'Piso quinto']);
        $incident = $this->incident($owner, [
            'reporter_name' => 'Jorge Morales', 'reporter_position' => 'Coordinador técnico',
            'area_id' => $area->id, 'zone_id' => $zone->id, 'location' => 'Oficina norte',
            'affected_assets' => 'UPS-8472', 'description' => 'Cable sin aislamiento',
            'failure_id' => IncidentOption::where('type', 'failure')->where('name', 'Falla eléctrica')->firstOrFail()->id,
            'priority_id' => IncidentOption::where('type', 'priority')->where('name', 'Alta')->firstOrFail()->id,
            'created_at' => '2026-10-10 08:47:00',
        ]);
        $incident->attachments()->create(['path' => 'unused.pdf', 'original_name' => 'informe-inspección.pdf', 'mime_type' => 'application/pdf', 'size' => 100]);
        $foreign = $this->incident(User::factory()->create(), [
            'reporter_name' => 'Jorge Morales', 'area_id' => $area->id, 'zone_id' => $zone->id,
            'failure_id' => $incident->failure_id, 'priority_id' => $incident->priority_id,
            'affected_assets' => 'UPS-8472', 'created_at' => '2026-10-10 08:47:00',
        ]);
        $this->actingAs($owner);
        foreach (['INC-000', '000001', 'morales', 'Coordinador', 'admin', 'Piso quinto', 'eléctrica', 'Alta', 'norte', 'aislamiento', 'UPS-8472', 'inspección.pdf', '10/10/2026', '10-10-2026', '10/2026', '2026-10-10', '08:47', 'Reportado'] as $search) {
            $this->get(route('incidents.index', ['search' => $search]))->assertOk()
                ->assertViewHas('incidents', fn ($rows) => $rows->modelKeys() === [$incident->id]);
        }
        $this->get(route('incidents.index', ['search' => 'sin coincidencias']))->assertViewHas('incidents', fn ($rows) => $rows->isEmpty());
        $this->actingAs($this->userWith(['incidents.view_all']))->get(route('incidents.index', ['search' => 'morales']))
            ->assertViewHas('incidents', fn ($rows) => $rows->modelKeys() === [$foreign->id, $incident->id]);
    }

    public function test_one_state_filter_handles_attention_activity_and_archive_and_removed_filters_do_not_hide_results(): void
    {
        $owner = $this->userWith(['incidents.view']);
        $reported = $this->incident($owner);
        $attending = $this->incident($owner, ['status' => 'en_atencion', 'is_active' => false]);
        $resolved = $this->incident($owner, ['status' => 'resuelto', 'is_active' => false]);
        $archived = $this->incident($owner, ['status' => 'resuelto', 'archived_at' => now()]);
        $this->actingAs($owner);
        foreach ([
            '' => [$archived->id, $resolved->id, $attending->id, $reported->id],
            'reportado' => [$reported->id], 'en_atencion' => [$attending->id],
            'resuelto' => [$archived->id, $resolved->id], 'activo' => [$archived->id, $reported->id],
            'inactivo' => [$resolved->id, $attending->id], 'archivado' => [$archived->id],
        ] as $status => $ids) {
            $this->get(route('incidents.index', ['status' => $status]))->assertOk()->assertViewHas('incidents', fn ($rows) => $rows->modelKeys() === $ids);
        }
        $response = $this->get(route('incidents.index', ['area_id' => 999, 'failure_id' => 999, 'from' => '1900-01-01', 'to' => '1900-01-02', 'activity' => 'inactive', 'archive' => 'archived']))->assertOk();
        $response->assertViewHas('incidents', fn ($rows) => $rows->total() === 4);
        foreach (['area_id', 'failure_id', 'from', 'to', 'activity', 'archive'] as $field) { $response->assertDontSee('name="'.$field.'"', false); }
        $this->get(route('incidents.index', ['search' => 'archivado']))->assertViewHas('incidents', fn ($rows) => $rows->modelKeys() === [$archived->id]);
    }

    public function test_priority_badges_and_activity_use_the_requested_colors(): void
    {
        $owner = $this->userWith(['incidents.view']);
        foreach (['Alta' => 'tone-danger', 'Media' => 'tone-warning', 'Baja' => 'tone-success'] as $name => $tone) {
            $priority = IncidentOption::where('type', 'priority')->where('name', $name)->firstOrFail();
            $incident = $this->incident($owner, ['priority_id' => $priority->id]);
            $this->assertSame($tone, $incident->priority_tone);
        }
        $this->incident($owner, ['is_active' => false]);
        $response = $this->actingAs($owner)->get(route('incidents.index'))->assertOk();
        $response->assertSee('incident-pill tone-success">Activo', false)->assertSee('incident-pill tone-warning">Inactivo', false);
    }

    public function test_pdf_embeds_the_login_logo_when_there_are_no_photos(): void
    {
        $owner = $this->userWith(['incidents.view']);
        $incident = $this->incident($owner);
        $response = $this->actingAs($owner)->get(route('incidents.pdf', $incident))->assertOk();
        [$width] = getimagesize(public_path('images/logo-login.png'));
        $this->assertStringContainsString('/Subtype /Image', $response->getContent());
        $this->assertStringContainsString('/Width '.$width, $response->getContent());
    }

    public function test_pdf_information_is_grouped_in_labeled_cards_without_losing_description_or_evidence(): void
    {
        $owner = $this->userWith(['incidents.view']);
        $incident = $this->incident($owner, ['description' => str_repeat('Detalle del incidente. ', 200).'FIN DEL REPORTE']);
        $incident->refresh()->load(['creator', 'area', 'zone', 'failure', 'impact', 'priority', 'attachments']);
        $html = view('incidents.pdf', [
            'incident' => $incident, 'logo' => '',
            'evidence' => collect([['name' => 'Inspección.pdf', 'size' => 1024, 'is_image' => false, 'source' => null]]),
        ])->render();
        foreach (['reporter', 'location', 'description', 'tracking', 'evidence'] as $section) {
            $this->assertStringContainsString('data-section="'.$section.'"', $html);
        }
        $this->assertStringContainsString(e($incident->description), $html);
        $this->assertStringContainsString('Inspección.pdf', $html);
        $this->assertStringContainsString('Estado de atención', $html);
        $this->assertStringContainsString('Actividad', $html);
    }

    public function test_incident_cards_keep_summary_filters_pagination_and_permission_based_actions(): void
    {
        $owner = $this->userWith(['incidents.view']);
        $first = $this->incident($owner);
        foreach (range(1, 15) as $index) {
            $this->incident($owner, ['description' => 'Revisión de cableado '.$index]);
        }
        $foreign = $this->incident(User::factory()->create());
        $response = $this->actingAs($owner)->get(route('incidents.index', ['search' => 'Revisión']))->assertOk();
        $response->assertSee('class="incident-cards"', false)->assertDontSee('<table', false)
            ->assertSee('Reportado por')->assertSee('Área / dependencia')->assertSee('Ubicación')
            ->assertSee('Estado de atención')->assertSee('Actividad')->assertDontSee($foreign->ticket);
        $this->assertSame(15, substr_count($response->getContent(), '<article class="incident-list-card"'));
        $response->assertSee('aria-label="Ver INC-', false)->assertDontSee('aria-label="Editar INC-', false)
            ->assertDontSee('aria-label="Inactivar INC-', false);
        $all = $this->get(route('incidents.index'))->assertOk();
        $all->assertSee('page=2', false);
        $this->get(route('incidents.index', ['page' => 2]))->assertSee($first->ticket)->assertDontSee($foreign->ticket);
        $this->get(route('incidents.index', ['search' => 'sin coincidencias']))->assertOk()
            ->assertSee('No hay incidentes para los filtros seleccionados.')->assertDontSee('<article class="incident-list-card"', false);
    }

    public function test_cards_and_detail_keep_edit_archive_controls_and_distinct_detail_sections(): void
    {
        $user = $this->userWith(['incidents.view', 'incidents.edit', 'incidents.toggle', 'incidents.archive']);
        $incident = $this->incident($user, ['status' => 'resuelto']);
        $this->actingAs($user)->get(route('incidents.index'))->assertOk()
            ->assertSee('aria-label="Acciones de '.$incident->ticket.'"', false)
            ->assertDontSee('<footer class="incident-card-footer"', false)
            ->assertSee('aria-label="Editar '.$incident->ticket.'"', false)
            ->assertSee('aria-label="Inactivar '.$incident->ticket.'"', false)
            ->assertSee('aria-label="Archivar '.$incident->ticket.'"', false);
        $response = $this->get(route('incidents.show', $incident))->assertOk()->assertSee('Generar PDF');
        foreach (['reporter', 'location', 'description', 'evidence', 'tracking'] as $section) {
            $response->assertSee('aria-labelledby="incident-'.$section.'-heading"', false);
        }
        $this->assertSame(5, substr_count($response->getContent(), '<section class="incident-detail-panel'));
        $response->assertSee('Sin evidencias adjuntas.')->assertSee($incident->description);
        $incident->update(['archived_at' => now()]);
        $archived = $this->get(route('incidents.index'))->assertOk();
        $archived->assertSee('aria-label="Restaurar '.$incident->ticket.'"', false)
            ->assertDontSee('aria-label="Editar '.$incident->ticket.'"', false)
            ->assertDontSee('aria-label="Inactivar '.$incident->ticket.'"', false);
    }
}
