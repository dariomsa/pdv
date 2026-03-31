@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="card-header">
     FACTURACIÓN
  </div>

  <div class="p-3 mb-4 rounded">
    <h5 class="celeste">Inscripciones</h5>

    @if ($errors->any())
      <div role="alert">
        <div class="bg-red-500 text-white font-bold rounded-t px-4 py-2 mx-4">
          Validation Errors
        </div>
        <div class="border border-t-0 border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mx-4">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($inscripcion && $inscripcion->participantes_temporal->count())

      <div class="table-responsive">
        <table id="tabla-participantes" class="table table-striped table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Nombres</th>
              <th>Documento</th>
              <th>Género</th>
              <th>Correo</th>
              <th>Fecha de Nacimiento</th>
              <th>Talla Camiseta</th>
            </tr>
          </thead>
          <tbody>
            @foreach($participantes as $index => $p)
              <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $p->nombres }} {{ $p->apellidos }}</td>
                <td>{{ $p->tipo_documento }} - {{ $p->numero_documento }}</td>
                <td>{{ ucfirst($p->genero) }}</td>
                <td>{{ $p->email }}</td>
                <td>{{ \Carbon\Carbon::parse($p->fecha_nacimiento)->format('d/m/Y') }}</td>
                <td>{{ $p->talla }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      {{-- ✅ IMPORTANTE: agrega id al form --}}
      <form id="form-finalizar" action="{{ route('admin.inscripciones.finalizar') }}" method="POST">
        @csrf

        <div class="row">
          <div class="col-md-7">
            <h5 class="celeste">Datos de facturación</h5>

            <div class="form-check mb-2">
              <input type="checkbox" class="form-check-input" id="usar_mismos" onclick="rellenarDatos()">
              <label class="form-check-label" for="usar_mismos">Desea utilizar los mismos datos de inscripción</label>
            </div>

            <div class="form-row">
              <div class="form-group col-md-3">
                <label>Tipo de Documento</label>
                {{-- ✅ required --}}
                <select name="fact_tipo_documento" class="form-control" id="fact_tipo_documento" required>
                  <option value="C">Cédula</option>
                  <option value="R">RUC</option>
                </select>
              </div>

              <div class="form-group col-md-3">
                <label>Documento</label>
                {{-- ✅ required + id ya existe --}}
                <input type="text" name="numero_doc_facturacion" class="form-control" id="documento_fact" value="" required>
              </div>

              <div class="form-group col-md-3">
                <label>Nombre</label>
                {{-- ✅ required --}}
                <input type="text" name="nombre_facturacion" class="form-control" id="nombre_fact" value="" required>
              </div>

              <div class="form-group col-md-3">
                <label>Apellido</label>
                {{-- ✅ required --}}
                <input type="text" name="apellido_facturacion" class="form-control" id="apellido_fact" value="" required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group col-md-4">
                <label>Email</label>
                {{-- ✅ required --}}
                <input type="email" name="email_facturacion" class="form-control" id="email_fact" value="" required>
              </div>

              <div class="form-group col-md-4">
                <label>Celular</label>
                {{-- ✅ required --}}
                <input type="text" name="telefono_facturacion" class="form-control" id="celular_fact" value="" required>
              </div>

              <div class="form-group col-md-4">
                <label>Dirección</label>
                {{-- ✅ required --}}
                <input type="text" name="direccion_facturacion" class="form-control" id="direccion_fact" value="" required>
              </div>
            </div>

            <div class="form-group">
              <label>Nota Adicional</label>
              <textarea name="nota_facturacion" class="form-control" rows="2"></textarea>
            </div>
          </div>

          <div class="col-md-5">
            <h5 class="celeste">Resumen de Pago</h5>
            <table class="table">
              <tr><th>SubTotal</th><td>${{ number_format($subtotal, 2) }}</td></tr>
              <tr><th>IVA</th><td>${{ number_format($iva_total, 2) }}</td></tr>
              <tr><th>Total</th><td><strong>${{ number_format($total, 2) }}</strong></td></tr>
            </table>

            <h5 class="celeste">Forma de pago</h5>
            <select name="forma_pago" class="form-control w-50" id="forma_pago_select" required>
              @foreach ($formasPago as $fp)
                <option value="{{ $fp->id }}" data-metodo="{{ strtolower($fp->metodo_pago) }}">
                  {{ $fp->metodo_pago }}
                </option>
              @endforeach
            </select>

            <br>

            <div class="form-group w-50" id="referencia_group">
              <label>Referencia</label>
              {{-- ✅ pon id y required dinámico --}}
              <input type="text" name="referencia" class="form-control" id="referencia_input">
            </div>

            {{-- ✅ cambia a type=button para controlar el submit con SweetAlert --}}
            <button type="button" id="btn-finalizar" class="btn btn-primary">
              Finalizar Inscripción
            </button>
			
			 @can('corporativas_access')
			<button type="button" id="btn-gratuita" class="btn btn-danger ms-2">
  Inscripción Gratuita
</button>

    @endcan
          </div>
        </div>
      </form>
    @endif
  </div>
</div>

{{-- ✅ SweetAlert2 CDN (ponlo una sola vez en tu layout si ya lo usas) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/**
 * ✅ JS COMPLETO (sin duplicar listeners)
 * - Rellenar datos con checkbox
 * - Toggle referencia según forma de pago
 * - SweetAlert + validación + submit a:
 *    - finalizar (btn-finalizar)
 *    - gratuita  (btn-gratuita)
 *
 * Requisitos en el Blade:
 *  - form id="form-finalizar" action="{{ route('admin.inscripciones.finalizar') }}"
 *  - botones:
 *      <button type="button" id="btn-finalizar" class="btn btn-primary">Finalizar Inscripción</button>
 *      <button type="button" id="btn-gratuita" class="btn btn-danger ms-2">Inscripción Gratuita</button>
 *  - SweetAlert2 cargado
 */

function rellenarDatos() {
  const participante = @json($primer ?? []);
  const chk = document.getElementById('usar_mismos');

  if (chk && chk.checked && participante) {
    document.getElementById('documento_fact').value = participante.numero_documento || '';
    document.getElementById('nombre_fact').value = participante.nombres || '';
    document.getElementById('apellido_fact').value = participante.apellidos || '';
    document.getElementById('email_fact').value = participante.email || '';
    document.getElementById('celular_fact').value = participante.celular || '';
    document.getElementById('direccion_fact').value = participante.direccion || '';
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById('form-finalizar');
  if (!form) return;

  // Botones
  const btnFinalizar = document.getElementById('btn-finalizar');
  const btnGratuita  = document.getElementById('btn-gratuita');

  // Actions
  const actionFinalizar = form.getAttribute('action'); // route finalizar
  const actionGratuita  = @json(route('admin.inscripciones.gratuita'));

  // Toggle referencia
  const select = document.getElementById('forma_pago_select');
  const referenciaGroup = document.getElementById('referencia_group');
  const referenciaInput = document.getElementById('referencia_input');

  function toggleReferencia() {
    if (!select || !referenciaGroup || !referenciaInput) return;

    const selectedOption = select.options[select.selectedIndex];
    const metodo = (selectedOption?.getAttribute('data-metodo') || '').toLowerCase();

    if (metodo === 'efectivo') {
      referenciaGroup.style.display = 'none';
      referenciaInput.required = false;
      referenciaInput.value = '';
    } else {
      referenciaGroup.style.display = 'block';
      referenciaInput.required = true;
    }
  }

  if (select) {
    select.addEventListener('change', toggleReferencia);
    toggleReferencia();
  }

  // Validación campos facturación
  const requiredFields = [
    { id: 'fact_tipo_documento', label: 'Tipo de Documento' },
    { id: 'documento_fact', label: 'Documento' },
    { id: 'nombre_fact', label: 'Nombre' },
    { id: 'apellido_fact', label: 'Apellido' },
    { id: 'email_fact', label: 'Email' },
    { id: 'celular_fact', label: 'Celular' },
    { id: 'direccion_fact', label: 'Dirección' },
  ];

  function validateFactura() {
    const faltantes = [];

    requiredFields.forEach(f => {
      const el = document.getElementById(f.id);
      if (!el) return;
      const val = (el.value || '').trim();
      if (!val) faltantes.push(f.label);
    });

    // Referencia (solo si está visible y required)
    if (referenciaInput && referenciaGroup) {
      const refVisible = referenciaGroup.style.display !== 'none';
      if (refVisible && referenciaInput.required && !(referenciaInput.value || '').trim()) {
        faltantes.push('Referencia');
      }
    }

    return faltantes;
  }

  async function submitWithConfirm({ action, title, text, confirmText }) {
    const faltantes = validateFactura();

    if (faltantes.length) {
      await Swal.fire({
        icon: 'warning',
        title: 'Faltan datos de facturación',
        html: `<div style="text-align:left">
                <p>Completa estos campos para continuar:</p>
                <ul>${faltantes.map(x => `<li>${x}</li>`).join('')}</ul>
              </div>`,
        confirmButtonText: 'Ok'
      });
      return;
    }

    // Validación HTML5 (email, required, etc.)
    if (!form.checkValidity()) {
      await Swal.fire({
        icon: 'warning',
        title: 'Revisa el formulario',
        text: 'Hay campos con formato inválido (por ejemplo email).',
        confirmButtonText: 'Ok'
      });
      return;
    }

    const result = await Swal.fire({
      icon: 'question',
      title,
      text,
      showCancelButton: true,
      confirmButtonText: confirmText,
      cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    // Cambiar action y enviar
    form.setAttribute('action', action);
    form.submit();

    // por si acaso, restaurar
    form.setAttribute('action', actionFinalizar);
  }

  // ✅ Finalizar normal
  if (btnFinalizar) {
    btnFinalizar.addEventListener('click', function (e) {
      e.preventDefault();
      submitWithConfirm({
        action: actionFinalizar,
        title: '¿Finalizar inscripción?',
        text: 'Se generará la facturación y se registrará el pago. ¿Deseas continuar?',
        confirmText: 'Sí, finalizar'
      });
    });
  }

  // ✅ Inscripción gratuita
  if (btnGratuita) {
    btnGratuita.addEventListener('click', function (e) {
      e.preventDefault();
      submitWithConfirm({
        action: actionGratuita,
        title: '¿Registrar inscripción GRATUITA?',
        text: 'Se generará la facturación y el pago en $0. ¿Deseas continuar?',
        confirmText: 'Sí, registrar'
      });
    });
  }
});
</script>

@endsection