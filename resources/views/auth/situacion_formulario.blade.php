@extends('auth.plantilla')

@section('contenido')
    <div class="container-emergencias">

        <header class="header-form">
            <h1>SITUACIONES DE EMERGENCIA</h1>
            <p>Formulario de registro de incidencias y situaciones de emergencia</p>
        </header>

        @if (session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('emergencias.guardar') }}" enctype="multipart/form-data" class="form-emergencias">
            @csrf

            <div class="form-grid">

                <div class="form-group">
                    <label>Comunidad Autónoma</label>
                    <select name="ccaa_id" id="ccaa_selector" required onchange="filtrarProvincias()">
                        <option value="">Seleccione una comunidad</option>
                        @foreach ($ccaaParaFormulario as $comunidad)
                            <option value="{{ $comunidad->c_id }}">
                                {{ $comunidad->c_comunidad_autonoma }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- PANEL DE PROVINCIAS (Checkboxes Múltiples) --}}
                <div class="form-group" id="grupo_provincia"
                    style="display:none; grid-column: span 2; background: #f9f9f9; padding: 15px; border-radius: 5px; border: 1px solid #ddd;">
                    <label style="margin-bottom: 10px; display: block;">Provincias afectadas:</label>

                    <div style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px dashed #ccc;">
                        <label style="font-weight: normal; cursor: pointer;">
                            <input type="checkbox" id="check_todos" onclick="marcarTodas(this)">
                            <strong>-- SELECCIONAR TODAS --</strong>
                        </label>
                    </div>

                    <div id="contenedor_checkboxes" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
                        {{-- Los checkboxes se llenarán con JavaScript --}}
                    </div>

                    {{-- Mensaje de error por si se le olvida marcar provincias --}}
                    @error('provincias_ids')
                        <span
                            style="color: red; font-size: 0.85em; margin-top: 10px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Nivel de gravedad</label>
                    <select name="nivel" required>
                        <option value="0">Normalidad</option>
                        <option value="1">Preemergencia / Alerta</option>
                        <option value="2">Situación 0</option>
                        <option value="3">Situación 1</option>
                        <option value="4">Situación 2</option>
                        <option value="5">Situación 3</option>
                    </select>
                </div>

                <div class="form-group doble">
                    <div>
                        <label>Fecha del Suceso</label>
                        <input type="date" name="fecha" required value="{{ date('Y-m-d') }}">
                    </div>

                    <div>
                        <label>Hora (del PDF/Correo)</label>
                        <input type="time" name="hora" required>
                    </div>
                </div>

            </div>

            <section class="documentacion">
                <h3>Documentación asociada</h3>
                <div class="document-selector">
                    <label>
                        <input type="radio" name="tipo_documento" value="pdf_oficial" checked onchange="toggleDoc()">
                        Documento PDF oficial
                    </label>
                    <label>
                        <input type="radio" name="tipo_documento" value="texto_correo" onchange="toggleDoc()">
                        Texto de correo institucional
                    </label>
                </div>

                <div id="caja_pdf" class="document-box">
                    <label style="font-size: 0.8rem; color: #666;">Adjuntar PDF original:</label>
                    <input type="file" name="archivo_pdf" accept=".pdf">
                </div>

                <div id="caja_correo" class="document-box hidden">
                    <textarea name="texto_correo" rows="5" placeholder="Pegue aquí el contenido del correo electrónico recibido..."></textarea>
                </div>
            </section>

            <div class="form-group full">
                <label>Descripción / Observaciones</label>
                <textarea name="descripcion" rows="4" placeholder="Detalles adicionales sobre la situación..."></textarea>
            </div>

            <div class="form-actions">
                <button type="submit">
                    Guardar y Actualizar Calendario
                </button>
            </div>

        </form>

    </div>

    <style>
        /* ... (Se mantienen tus estilos exactos) ... */
        .container-emergencias {
            max-width: 900px;
            margin: 40px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e5e5;
            font-family: sans-serif;
        }

        .header-form h1 {
            margin: 0;
            font-size: 1.7rem;
            color: #2c2c2c;
        }

        .header-form p {
            margin-top: 6px;
            color: #666;
            font-size: 0.9rem;
        }

        .alert-success {
            margin-top: 20px;
            padding: 15px;
            background: #f0f8f2;
            border: 1px solid #cfe7d6;
            border-radius: 6px;
            color: #2e6f42;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-top: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 0.9rem;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 12px;
            border: 1px solid #d4d4d4;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .form-group.doble {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .documentacion {
            margin-top: 30px;
            padding: 20px;
            border: 1px solid #e3e3e3;
            border-radius: 6px;
            background: #fafafa;
        }

        .hidden {
            display: none;
        }

        .form-actions {
            margin-top: 35px;
            text-align: right;
        }

        .form-actions button {
            background: #5f666d;
            color: white;
            border: none;
            padding: 12px 35px;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>

    <script>
        // Pasamos las provincias desde Laravel
        const listaProvincias = @json($provinciasParaFormulario);

        function filtrarProvincias() {
            const ccaaId = document.getElementById('ccaa_selector').value;
            const contenedor = document.getElementById('contenedor_checkboxes');
            const grupoProv = document.getElementById('grupo_provincia');
            const checkTodos = document.getElementById('check_todos');

            // Limpiamos contenido anterior y reseteamos el check de todos
            contenedor.innerHTML = '';
            checkTodos.checked = false;

            // Filtramos provincias de la CCAA elegida
            const filtradas = listaProvincias.filter(p => p.c_id == ccaaId);

            if (filtradas.length > 0) {
                // Usamos display: block porque el contenedor ahora ocupa 2 columnas completas
                grupoProv.style.display = 'block';

                filtradas.forEach(p => {
                    let div = document.createElement('div');
                    // IMPORTANTE: el name es 'provincias_ids[]' (un array para que Laravel lo entienda)
                    div.innerHTML = `
                <label style="font-weight: normal; cursor: pointer;">
                    <input type="checkbox" name="provincias_ids[]" value="${p.p_id}" class="check-provincia">
                    ${p.p_provincia}
                </label>
            `;
                    contenedor.appendChild(div);
                });
            } else {
                // Para Madrid o Portugal, se oculta todo
                grupoProv.style.display = 'none';
            }
        }

        // Función que marca o desmarca todo según el checkbox maestro
        function marcarTodas(fuente) {
            const checkboxes = document.querySelectorAll('.check-provincia');
            checkboxes.forEach(c => c.checked = fuente.checked);
        }

        function toggleDoc() {
            const pdf = document.querySelector('input[value="pdf_oficial"]').checked;
            document.getElementById('caja_pdf').classList.toggle('hidden', !pdf);
            document.getElementById('caja_correo').classList.toggle('hidden', pdf);
        }
    </script>
@endsection
