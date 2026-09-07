<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Procesos y subprocesos dejan de estar escritos en el codigo y pasan a
     * ser tablas administrables: se les puede cambiar el nombre, reordenar
     * o desactivar sin tocar una linea de PHP.
     *
     * Los indicadores dejan de guardar el texto y pasan a apuntar por
     * llave foranea, asi que renombrar un proceso se refleja solo en todos
     * los indicadores que lo usan.
     */
    private const PROCESSES = [
        'AUDITORIA CORPORATIVA',
        'GESTION ADMINISTRATIVA',
        'GESTION COMERCIAL',
        'GESTION DE PROYECTOS Y OPERACIONES',
        'GESTION DE TALENTO HUMANO',
        'GESTION DEL RIESGO',
        'GESTION ESTRATEGICA',
        'GESTION FINANCIERA',
        'GESTION JURIDICA',
        'SISTEMA DE GESTION AMBIENTAL',
        'SISTEMA DE GESTION DE SST',
        'SISTEMA DE GESTION DOCUMENTAL',
    ];

    /** [codigo, nombre] tal como vienen del documento del SGC. */
    private const SUBPROCESSES = [
        ['E01', 'GESTION ESTRATEGICA'],
        ['E02', 'GESTION DE RIESGOS'],
        ['E03', 'GESTION DE CONTROL'],
        ['E04', 'ADMINISTRACION DEL SGC'],
        ['M01', 'GESTION COMERCIAL'],
        ['M02', 'GESTION DE PROYECTOS'],
        ['M03', 'OPERACION DE SOLUCIONES'],
        ['M04', 'ESTRUCTURACION DE PROYECTOS'],
        ['A01', 'ABASTECIMIENTO - COMPRAS'],
        ['A01', 'ABASTECIMIENTO - LOGISTICA'],
        ['A02', 'SERVICIOS ADMINISTRATIVOS'],
        ['A03', 'GESTION FINANCIERA'],
        ['A04', 'GESTION CONTABLE'],
        ['A05', 'GESTION HUMANA'],
        ['A06', 'SISTEMA DE GESTION DE SEGURIDAD Y SALUD EN EL TRABAJO SG-SST'],
        ['A07', 'GESTION DE COMUNICACIONES'],
        ['A08', 'GESTION JURIDICA'],
        ['A09', 'GESTION INFORMATICA'],
        ['A10', 'GESTION AMBIENTAL'],
    ];

    /** Llaves de texto usadas antes => como ubicarlas ahora. */
    private const LEGACY_PROCESS_MAP = [
        'auditoria_corporativa' => 'AUDITORIA CORPORATIVA',
        'gestion_administrativa' => 'GESTION ADMINISTRATIVA',
        'gestion_comercial' => 'GESTION COMERCIAL',
        'gestion_proyectos_operaciones' => 'GESTION DE PROYECTOS Y OPERACIONES',
        'gestion_talento_humano' => 'GESTION DE TALENTO HUMANO',
        'gestion_riesgo' => 'GESTION DEL RIESGO',
        'gestion_estrategica' => 'GESTION ESTRATEGICA',
        'gestion_financiera' => 'GESTION FINANCIERA',
        'gestion_juridica' => 'GESTION JURIDICA',
        'sistema_gestion_ambiental' => 'SISTEMA DE GESTION AMBIENTAL',
        'sistema_gestion_sst' => 'SISTEMA DE GESTION DE SST',
        'sistema_gestion_documental' => 'SISTEMA DE GESTION DOCUMENTAL',
    ];

    public function up(): void
    {
        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('subprocesses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable()->index();
            $table->string('name', 200);
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        $now = now();

        foreach (self::PROCESSES as $i => $name) {
            DB::table('processes')->insert([
                'name' => $name, 'position' => ($i + 1) * 10, 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        foreach (self::SUBPROCESSES as $i => [$code, $name]) {
            DB::table('subprocesses')->insert([
                'code' => $code, 'name' => $name, 'position' => ($i + 1) * 10, 'is_active' => true,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        Schema::table('indicators', function (Blueprint $table) {
            $table->foreignId('process_id')->nullable()->after('category')->constrained('processes')->nullOnDelete();
            $table->foreignId('subprocess_id')->nullable()->after('process_id')->constrained('subprocesses')->nullOnDelete();
        });

        $this->migrateExistingValues();

        Schema::table('indicators', function (Blueprint $table) {
            $table->dropIndex(['process']);
            $table->dropIndex(['subprocess']);
            $table->dropColumn(['process', 'subprocess']);
        });
    }

    /**
     * Traslada lo que ya estaba guardado como texto a la nueva llave foranea,
     * para que ningun indicador pierda su clasificacion.
     */
    private function migrateExistingValues(): void
    {
        $processIds = DB::table('processes')->pluck('id', 'name');

        foreach (self::LEGACY_PROCESS_MAP as $legacyKey => $name) {
            if (isset($processIds[$name])) {
                DB::table('indicators')
                    ->where('process', $legacyKey)
                    ->update(['process_id' => $processIds[$name]]);
            }
        }

        // Los subprocesos se guardaban por codigo ('A07'), con dos variantes
        // para A01 que se distinguian por sufijo.
        foreach (DB::table('indicators')->whereNotNull('subprocess')->get(['id', 'subprocess']) as $row) {
            $legacy = (string) $row->subprocess;

            $match = match ($legacy) {
                'A01_COMPRAS' => DB::table('subprocesses')->where('name', 'ABASTECIMIENTO - COMPRAS')->value('id'),
                'A01_LOGISTICA' => DB::table('subprocesses')->where('name', 'ABASTECIMIENTO - LOGISTICA')->value('id'),
                default => DB::table('subprocesses')->where('code', $legacy)->value('id'),
            };

            if ($match) {
                DB::table('indicators')->where('id', $row->id)->update(['subprocess_id' => $match]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->string('process', 60)->nullable()->index()->after('category');
            $table->string('subprocess', 60)->nullable()->index()->after('process');
        });

        // Devolver los valores de texto antes de soltar las llaves foraneas.
        $names = DB::table('processes')->pluck('name', 'id');
        $legacyByName = array_flip(self::LEGACY_PROCESS_MAP);

        foreach ($names as $id => $name) {
            if (isset($legacyByName[$name])) {
                DB::table('indicators')->where('process_id', $id)->update(['process' => $legacyByName[$name]]);
            }
        }

        foreach (DB::table('subprocesses')->get(['id', 'code', 'name']) as $sub) {
            $legacy = match ($sub->name) {
                'ABASTECIMIENTO - COMPRAS' => 'A01_COMPRAS',
                'ABASTECIMIENTO - LOGISTICA' => 'A01_LOGISTICA',
                default => $sub->code,
            };

            DB::table('indicators')->where('subprocess_id', $sub->id)->update(['subprocess' => $legacy]);
        }

        Schema::table('indicators', function (Blueprint $table) {
            $table->dropConstrainedForeignId('process_id');
            $table->dropConstrainedForeignId('subprocess_id');
        });

        Schema::dropIfExists('subprocesses');
        Schema::dropIfExists('processes');
    }
};
