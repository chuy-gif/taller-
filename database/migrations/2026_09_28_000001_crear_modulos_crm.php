<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Un negocio separa la información de los equipos que se registren.
        Schema::create('negocios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('negocio_id')->nullable()->constrained('negocios');
            $table->string('rol', 20)->default('usuario_final');
            $table->boolean('activo')->default(true);
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios');
            $table->foreignId('responsable_id')->constrained('users');
            $table->string('nombre', 100);
            $table->string('canal', 20);
            $table->string('correo', 150)->nullable();
            $table->string('telefono', 25)->nullable();
            $table->text('alergias')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->index(['negocio_id', 'activo']);
        });

        Schema::create('contactos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('nombre', 100);
            $table->string('cargo', 80)->nullable();
            $table->string('correo', 150)->nullable();
            $table->string('telefono', 25)->nullable();
            $table->boolean('principal')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('responsable_id')->constrained('users');
            $table->foreignId('cocina_id')->nullable()->constrained('users');
            $table->string('producto', 100);
            $table->string('evento', 60);
            $table->unsignedInteger('porciones');
            $table->decimal('monto', 12, 2);
            $table->decimal('anticipo', 12, 2)->default(0);
            $table->date('fecha_entrega');
            $table->string('hora_entrega', 5);
            $table->string('etapa', 20)->default('consulta');
            $table->string('estado', 20)->default('abierta');
            $table->text('notas')->nullable();
            $table->text('motivo_perdida')->nullable();
            $table->timestamp('ultima_actividad');
            $table->timestamp('cerrada_en')->nullable();
            $table->timestamps();
            $table->index(['negocio_id', 'estado', 'fecha_entrega']);
        });

        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('venta_id')->nullable()->constrained('ventas');
            $table->foreignId('autor_id')->constrained('users');
            $table->string('tipo', 30);
            $table->string('asunto', 120);
            $table->text('descripcion');
            $table->timestamp('programada_para')->nullable();
            $table->boolean('completada')->default(false);
            $table->timestamps();
            $table->index(['negocio_id', 'completada', 'programada_para']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actividades');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('contactos');
        Schema::dropIfExists('clientes');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('negocio_id');
            $table->dropColumn(['rol', 'activo']);
        });
        Schema::dropIfExists('negocios');
    }
};
