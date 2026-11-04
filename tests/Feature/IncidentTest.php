<?php

namespace Tests\Feature;

use App\Mail\IncidentCommentNotificationMail;
use App\Mail\IncidentRiskNotificationMail;
use App\Mail\IncidentUpdatedNotificationMail;
use App\Models\Incident;
use App\Models\IncidentOption;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
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

        $data['responsible_user_id'] = User::factory()->create(['position' => 'Coordinador'])->id;

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
        $this->actingAs($user)->post(route('incidents.store'), [])->assertSessionHasErrors(['failure_id', 'location', 'description', 'responsible_user_id']);
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
        $this->get(route('incidents.edit', $incident))->assertSee('value="Jorge Morales"', false)->assertSee('value="TI"', false)->assertDontSee('Estado de atención');
        $this->actingAs($editor)->put(route('incidents.update', $incident), $data)->assertSessionHasNoErrors();
        $this->assertSame('en_atencion', $incident->fresh()->status);
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
        $this->assertStringNotContainsString('Estado de atención', $html);
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
            ->assertSee('Responsable asignado para notificación')
            ->assertDontSee('Estado de atención')->assertSee('Actividad')->assertDontSee($foreign->ticket);
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
        $response = $this->get(route('incidents.show', $incident))->assertOk()->assertSee('Generar PDF')->assertDontSee('Estado de atención');
        foreach (['reporter', 'location', 'description', 'evidence', 'tracking', 'comments'] as $section) {
            $response->assertSee('aria-labelledby="incident-'.$section.'-heading"', false);
        }
        $this->assertSame(6, substr_count($response->getContent(), '<section class="incident-detail-panel'));
        $response->assertSee('Sin evidencias adjuntas.')->assertSee($incident->description);
        $incident->update(['archived_at' => now()]);
        $archived = $this->get(route('incidents.index'))->assertOk();
        $archived->assertSee('aria-label="Restaurar '.$incident->ticket.'"', false)
            ->assertDontSee('aria-label="Editar '.$incident->ticket.'"', false)
            ->assertDontSee('aria-label="Inactivar '.$incident->ticket.'"', false);
    }

    public function test_prioritized_risk_stores_responsible_and_sends_notification_mail(): void
    {
        Mail::fake();

        $financiero = User::factory()->create([
            'position' => 'Gerente financiero',
            'is_active' => true,
            'email' => 'financiero@colvatel.com.co',
        ]);

        $risk = IncidentOption::where('type', 'prioritized_risk')
            ->where('name', 'Problemas de liquidez')
            ->firstOrFail();
        $this->assertSame('Gerente financiero', $risk->responsible_position);

        $creator = $this->userWith(['incidents.view', 'incidents.create']);
        $postData = $this->data();
        $postData['failure_id'] = $risk->id;
        $postData['failure_type'] = 'risk';
        $postData['responsible_user_id'] = $financiero->id;

        $response = $this->actingAs($creator)->post(route('incidents.store'), $postData);
        $response->assertSessionHasNoErrors()->assertRedirect();

        $incident = Incident::latest('id')->firstOrFail();
        $this->assertSame('risk', $incident->failure_type);
        $this->assertSame($financiero->id, $incident->responsible_user_id);
        $this->assertSame('Gerente financiero', $incident->responsible_position);
        $this->assertSame(trim($financiero->name.' '.$financiero->last_name), $incident->responsible_name);

        Mail::assertSent(IncidentRiskNotificationMail::class, function ($mail) use ($incident, $financiero) {
            return $mail->incident->id === $incident->id
                && $mail->hasTo($financiero->email);
        });

        $this->get(route('incidents.show', $incident))
            ->assertOk()
            ->assertSee('Detalles de Incidente o Riesgo')
            ->assertSee('Riesgo priorizado')
            ->assertSee('Problemas de liquidez')
            ->assertSee('Gerente financiero')
            ->assertSee(trim($financiero->name.' '.$financiero->last_name));
    }

    public function test_prioritized_risks_and_responsibles_are_configurable_in_settings(): void
    {
        $admin = $this->userWith(['incidents.settings', 'incidents.view_all']);

        $this->actingAs($admin)->post(route('incidents.options.store'), [
            'type' => 'prioritized_risk',
            'name' => 'Fuga de capital',
            'sort_order' => 10,
            'responsible_position' => 'Gerente financiero',
        ])->assertSessionHasNoErrors();

        $newRisk = IncidentOption::where('type', 'prioritized_risk')->where('name', 'Fuga de capital')->firstOrFail();
        $this->assertSame('Gerente financiero', $newRisk->responsible_position);

        $this->put(route('incidents.options.update', $newRisk), [
            'name' => 'Fuga de capital crítica',
            'sort_order' => 12,
            'responsible_position' => 'VP Operaciones',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Fuga de capital crítica', $newRisk->fresh()->name);
        $this->assertSame('VP Operaciones', $newRisk->fresh()->responsible_position);
    }

    public function test_create_incident_form_unifies_responsible_filter_into_one_and_maps_automatically(): void
    {
        $creator = $this->userWith(['incidents.view', 'incidents.create']);
        $lead = User::factory()->create([
            'position' => 'Gerente financiero',
            'is_active' => true,
            'name' => 'Alfonso',
            'last_name' => 'Gomez',
            'email' => 'alfonso.gomez@example.com',
        ]);

        $risk = IncidentOption::where('type', 'prioritized_risk')
            ->where('responsible_position', 'Gerente financiero')
            ->firstOrFail();

        $response = $this->actingAs($creator)->get(route('incidents.create'))->assertOk();

        // 1. Should have the unified combobox and single label
        $response->assertSee('id="responsible_combobox_input"', false);
        $response->assertSee('id="responsible_dropdown"', false);
        $response->assertSee('id="responsible_user_id"', false);
        $response->assertSee('Persona responsable <span class="incident-required">*</span>', false);

        // 2. Should NOT have the old separate "Buscar persona responsable" search input
        $response->assertDontSee('id="responsible_search"', false);
        $response->assertDontSee('Buscar persona responsable', false);

        // 3. Risk option should include data-user-id mapping to the responsible user
        $response->assertSee('data-user-id="'.$lead->id.'"', false);
        $response->assertSee('data-position="Gerente financiero"', false);

        // 4. View has the automatic badge and dropdown options
        $response->assertSee('id="responsible_auto_badge"', false);
        $response->assertSee('Alfonso Gomez', false);
    }

    public function test_general_incident_notifies_when_responsible_user_is_selected_and_does_not_when_not_set(): void
    {
        Mail::fake();

        $tiLead = User::factory()->create([
            'position' => 'Director TI',
            'is_active' => true,
            'email' => 'director.ti@example.com',
        ]);

        $generalFailure = IncidentOption::create([
            'type' => 'failure',
            'name' => 'Falla de conectividad',
            'sort_order' => 1,
            'responsible_position' => null,
            'is_active' => true,
        ]);

        $creator = $this->userWith(['incidents.view', 'incidents.create']);

        // Case 1: General incident without selecting a responsible user fails validation
        $data1 = $this->data();
        $data1['failure_id'] = $generalFailure->id;
        $data1['failure_type'] = 'general';
        $data1['responsible_user_id'] = null;

        $this->actingAs($creator)->post(route('incidents.store'), $data1)->assertSessionHasErrors('responsible_user_id');
        Mail::assertNothingSent();

        // Case 2: General incident with responsible user selected by the user
        $data2 = $this->data();
        $data2['failure_id'] = $generalFailure->id;
        $data2['failure_type'] = 'general';
        $data2['responsible_user_id'] = $tiLead->id;

        $this->actingAs($creator)->post(route('incidents.store'), $data2)->assertSessionHasNoErrors()->assertRedirect();
        $inc2 = Incident::latest('id')->firstOrFail();
        $this->assertSame('general', $inc2->failure_type);
        $this->assertSame($tiLead->id, $inc2->responsible_user_id);
        $this->assertSame('Director TI', $inc2->responsible_position);
        $this->assertSame(trim($tiLead->name.' '.$tiLead->last_name), $inc2->responsible_name);

        Mail::assertSent(IncidentRiskNotificationMail::class, function ($mail) use ($inc2, $tiLead) {
            return $mail->incident->id === $inc2->id
                && $mail->hasTo($tiLead->email)
                && str_contains($mail->envelope()->subject, 'Alerta de Incidente: Falla de conectividad');
        });

        $this->get(route('incidents.show', $inc2))
            ->assertOk()
            ->assertSee('Incidente general')
            ->assertSee(trim($tiLead->name.' '.$tiLead->last_name))
            ->assertSee('Director TI');
    }

    public function test_updating_incident_with_new_responsible_sends_notification_mail_to_new_responsible(): void
    {
        Mail::fake();

        $creator = $this->userWith(['incidents.view', 'incidents.edit']);
        $initialResponsible = User::factory()->create([
            'is_active' => true,
            'name' => 'Felipe',
            'last_name' => 'Morales',
            'position' => 'Jefe de Redes',
            'email' => 'felipe.morales@example.com',
        ]);
        $newResponsible = User::factory()->create([
            'is_active' => true,
            'name' => 'Sandra',
            'last_name' => 'López',
            'position' => 'Gerente de Seguridad',
            'email' => 'sandra.lopez@example.com',
        ]);

        $incident = $this->incident($creator, [
            'responsible_user_id' => $initialResponsible->id,
            'responsible_name' => 'Felipe Morales',
            'responsible_position' => $initialResponsible->position,
            'description' => 'Descripción original',
        ]);

        $updateData = $this->data();
        $updateData['responsible_user_id'] = $initialResponsible->id;
        $updateData['description'] = 'Descripción actualizada para prueba de correo';
        $updateData['status'] = 'en_atencion';

        // 1. Edit without changing responsible -> sends IncidentUpdatedNotificationMail to $initialResponsible
        $this->actingAs($creator)->put(route('incidents.update', $incident), $updateData)
            ->assertRedirect()
            ->assertSessionHas('success', 'Incidente actualizado y notificación enviada a las personas involucradas.');

        Mail::assertSent(IncidentUpdatedNotificationMail::class, function ($mail) use ($incident, $initialResponsible, $creator) {
            return $mail->incident->id === $incident->id
                && $mail->hasTo($initialResponsible->email)
                && $mail->recipient->id === $initialResponsible->id
                && $mail->editor->id === $creator->id
                && collect($mail->changes)->contains(fn ($c) => $c['field'] === 'Estado' && $c['new'] === 'En atención');
        });

        // Verify template renders with logo, typography, changes and button
        $mailable = new IncidentUpdatedNotificationMail($incident->fresh(), $initialResponsible, $creator, [
            ['field' => 'Estado', 'old' => 'Reportado', 'new' => 'En atención'],
            ['field' => 'Descripción', 'old' => null, 'new' => 'Descripción actualizada'],
        ]);
        $rendered = $mailable->render();
        $this->assertStringContainsString('logo-login.png', $rendered);
        $this->assertStringContainsString("'Inter', Arial, Helvetica, sans-serif", $rendered);
        $this->assertStringContainsString('Reporte '.$incident->ticket.' actualizado', $rendered);
        $this->assertStringContainsString('MODIFICACIONES REALIZADAS', $rendered);
        $this->assertStringContainsString('En atención', $rendered);
        $this->assertStringContainsString('Ver detalle del reporte en ColvaOne', $rendered);
        $this->assertStringContainsString('color: #ffffff !important', $rendered);

        Mail::fake(); // Reset fake to test responsible change

        // 2. Edit changing responsible to $newResponsible -> sends IncidentRiskNotificationMail to $newResponsible
        $updateData['responsible_user_id'] = $newResponsible->id;

        $this->actingAs($creator)->put(route('incidents.update', $incident), $updateData)
            ->assertRedirect()
            ->assertSessionHas('success', 'Incidente actualizado y notificación enviada al nuevo responsable asignado.');

        Mail::assertSent(IncidentRiskNotificationMail::class, function ($mail) use ($incident, $newResponsible) {
            return $mail->incident->id === $incident->id
                && $mail->hasTo($newResponsible->email)
                && $mail->recipient->id === $newResponsible->id;
        });

        Mail::assertNotSent(IncidentRiskNotificationMail::class, function ($mail) use ($initialResponsible) {
            return $mail->hasTo($initialResponsible->email);
        });

        $this->assertSame($newResponsible->id, $incident->fresh()->responsible_user_id);

        // 3. Edit changing ONLY location (e.g. Parqueadera -> Parqueadero) -> only 1 change entry
        Mail::fake();
        $singleChangeData = $this->data();
        $singleChangeData['responsible_user_id'] = $newResponsible->id;
        $singleChangeData['priority_id'] = $incident->priority_id;
        $singleChangeData['failure_id'] = $incident->failure_id;
        $singleChangeData['zone_id'] = $incident->zone_id;
        $singleChangeData['impact_id'] = $incident->impact_id;
        $singleChangeData['description'] = $incident->fresh()->description;
        $singleChangeData['affected_assets'] = $incident->fresh()->affected_assets;
        $singleChangeData['location'] = 'Parqueadero nuevo';

        $this->actingAs($creator)->put(route('incidents.update', $incident), $singleChangeData)
            ->assertRedirect()
            ->assertSessionHas('success', 'Incidente actualizado y notificación enviada a las personas involucradas.');

        Mail::assertSent(IncidentUpdatedNotificationMail::class, function ($mail) {
            $changes = collect($mail->changes);
            return $changes->count() === 1
                && $changes->first()['field'] === 'Ubicación específica'
                && $changes->first()['new'] === 'Parqueadero nuevo';
        });
    }

    public function test_involved_users_can_add_comments_to_incident(): void
    {
        Mail::fake();

        $creator = $this->userWith(['incidents.view']);
        $responsible = $this->userWith(['incidents.view']);
        $responsible->update([
            'is_active' => true,
            'name' => 'Carlos',
            'last_name' => 'Pérez',
            'position' => 'Gerente de operaciones',
        ]);

        $incident = $this->incident($creator, [
            'responsible_user_id' => $responsible->id,
            'responsible_name' => 'Carlos Pérez',
            'responsible_position' => $responsible->position,
        ]);

        // Creator adds a comment -> email sent to responsible
        $this->actingAs($creator)
            ->post(route('incidents.comments.add', $incident), [
                'comment' => 'Se requiere visita técnica urgente para validar daños.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(IncidentCommentNotificationMail::class, function ($mail) use ($incident, $responsible, $creator) {
            return $mail->incident->id === $incident->id
                && $mail->hasTo($responsible->email)
                && $mail->recipient->id === $responsible->id
                && $mail->author->id === $creator->id
                && str_contains($mail->comment->comment, 'Se requiere visita técnica');
        });

        $this->assertDatabaseHas('incident_comments', [
            'incident_id' => $incident->id,
            'user_id' => $creator->id,
            'comment' => 'Se requiere visita técnica urgente para validar daños.',
        ]);

        // Detail page reflects the comment with "Reportante" badge
        $this->actingAs($creator)
            ->get(route('incidents.show', $incident))
            ->assertOk()
            ->assertSee('Comentarios de las personas involucradas (1)')
            ->assertSee('Se requiere visita técnica urgente para validar daños.')
            ->assertSee('Reportante');

        // Responsible user adds a comment -> email sent to creator
        $this->actingAs($responsible)
            ->post(route('incidents.comments.add', $incident), [
                'comment' => 'Recibido. Técnico asignado en camino a la sede.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertSent(IncidentCommentNotificationMail::class, function ($mail) use ($incident, $creator, $responsible) {
            return $mail->incident->id === $incident->id
                && $mail->hasTo($creator->email)
                && $mail->recipient->id === $creator->id
                && $mail->author->id === $responsible->id
                && str_contains($mail->comment->comment, 'Recibido. Técnico asignado');
        });

        $this->assertDatabaseHas('incident_comments', [
            'incident_id' => $incident->id,
            'user_id' => $responsible->id,
            'comment' => 'Recibido. Técnico asignado en camino a la sede.',
        ]);

        // Render the email view directly to verify template, logo, typography and button
        $commentModel = $incident->comments()->latest('id')->firstOrFail();
        $mailable = new IncidentCommentNotificationMail($incident, $commentModel, $creator, $responsible);
        $renderedMail = $mailable->render();

        $this->assertStringContainsString('logo-login.png', $renderedMail);
        $this->assertStringContainsString("'Inter', Arial, Helvetica, sans-serif", $renderedMail);
        $this->assertStringContainsString('Nuevo comentario en el incidente '.$incident->ticket, $renderedMail);
        $this->assertStringContainsString('Recibido. Técnico asignado en camino a la sede.', $renderedMail);
        $this->assertStringContainsString('Ver detalle del reporte en ColvaOne', $renderedMail);
        $this->assertStringContainsString('color: #ffffff !important', $renderedMail);

        // Detail page reflects both comments with "Responsable" badge
        $this->actingAs($creator)
            ->get(route('incidents.show', $incident))
            ->assertOk()
            ->assertSee('Comentarios de las personas involucradas (2)')
            ->assertSee('Recibido. Técnico asignado en camino a la sede.')
            ->assertSee('Responsable');

        // Validation test: empty comment
        $this->actingAs($creator)
            ->post(route('incidents.comments.add', $incident), [
                'comment' => '',
            ])
            ->assertSessionHasErrors('comment');

        // Archived incident cannot receive comments
        $incident->update(['status' => 'resuelto', 'archived_at' => now()]);
        $this->actingAs($creator)
            ->post(route('incidents.comments.add', $incident), [
                'comment' => 'Intento en archivado',
            ])
            ->assertForbidden();
    }

    public function test_reports_view_renders_kpis_and_distributions_with_visibility_scoping(): void
    {
        $user = $this->userWith(['incidents.view']);
        $foreignUser = $this->userWith(['incidents.view']);

        $zone = IncidentOption::firstOrCreate(['type' => 'zone', 'name' => 'ETB Salitre'], ['is_active' => true]);
        $prioAlta = IncidentOption::firstOrCreate(['type' => 'priority', 'name' => 'Alta'], ['is_active' => true]);

        $mine = $this->incident($user, [
            'zone_id' => $zone->id,
            'priority_id' => $prioAlta->id,
            'failure_type' => 'risk',
        ]);

        $foreign = $this->incident($foreignUser, [
            'description' => 'Incidente de otro usuario confidencial',
        ]);

        $response = $this->actingAs($user)->get(route('incidents.reports'))
            ->assertOk()
            ->assertSee('Reportes, estadísticas y métricas operativas')
            ->assertSee('Total de Reportes')
            ->assertSee('Riesgos Priorizados')
            ->assertSee('Distribución por Sede')
            ->assertSee('ETB Salitre')
            ->assertSee($mine->ticket)
            ->assertDontSee($foreign->ticket)
            ->assertDontSee('Incidente de otro usuario confidencial');

        // Test filtering by classification
        $this->get(route('incidents.reports', ['failure_type' => 'general']))
            ->assertOk()
            ->assertDontSee($mine->ticket);

        $this->get(route('incidents.reports', ['failure_type' => 'risk']))
            ->assertOk()
            ->assertSee($mine->ticket);
    }

    public function test_reports_can_be_exported_to_excel_and_pdf(): void
    {
        $user = $this->userWith(['incidents.view']);
        $incident = $this->incident($user, ['failure_type' => 'risk']);

        // Excel export
        $excelResponse = $this->actingAs($user)->get(route('incidents.reports.excel'));
        $excelResponse->assertOk();
        $this->assertTrue(
            str_contains($excelResponse->headers->get('content-type') ?? '', 'spreadsheetml')
            || str_contains($excelResponse->headers->get('content-type') ?? '', 'octet-stream')
        );

        // PDF export
        $pdfResponse = $this->actingAs($user)->get(route('incidents.reports.pdf'));
        $pdfResponse->assertOk();
        $this->assertSame('application/pdf', $pdfResponse->headers->get('content-type'));
    }
}
