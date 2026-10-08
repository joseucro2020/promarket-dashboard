@extends('layouts/contentLayoutMaster')

@section('title', 'Configuración de la App')

@section('content')
<section id="app-config-form">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header border-bottom p-1">
          <div class="head-label">
            <h4 class="mb-0">Configuración de la App (Diseño y Módulos)</h4>
          </div>
        </div>
        <div class="card-body mt-2">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif

          <form action="{{ route('app-config.store') }}" method="POST">
            @csrf

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#branding" role="tab">1. Branding (Marca)</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#home_layout" role="tab">2. Layout de Inicio</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#social" role="tab">3. Redes y Contacto</a>
              </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content mt-2">
              
              <!-- Tab 1: Branding -->
              <div class="tab-pane active" id="branding" role="tabpanel">
                <div class="form-group row">
                  <label for="app_name" class="col-sm-2 col-form-label">Nombre de la App</label>
                  <div class="col-sm-10">
                    <input type="text" class="form-control" id="app_name" name="app_name" value="{{ $configs['app_name'] ?? 'Mi Tienda' }}">
                  </div>
                </div>
                <div class="form-group row">
                  <label for="primary_color" class="col-sm-2 col-form-label">Color Primario (Hex)</label>
                  <div class="col-sm-10">
                    <input type="color" class="form-control" style="max-width: 100px;" id="primary_color" name="primary_color" value="{{ $configs['primary_color'] ?? '#007bff' }}">
                  </div>
                </div>
                <div class="form-group row">
                  <label for="secondary_color" class="col-sm-2 col-form-label">Color Secundario (Hex)</label>
                  <div class="col-sm-10">
                    <input type="color" class="form-control" style="max-width: 100px;" id="secondary_color" name="secondary_color" value="{{ $configs['secondary_color'] ?? '#6c757d' }}">
                  </div>
                </div>
                <div class="form-group row">
                  <label for="logo_url" class="col-sm-2 col-form-label">URL del Logo</label>
                  <div class="col-sm-10">
                    <input type="text" class="form-control" id="logo_url" name="logo_url" value="{{ $configs['logo_url'] ?? '' }}" placeholder="https://dominio.com/logo.png">
                  </div>
                </div>
              </div>

              <!-- Tab 2: Home Layout -->
              <div class="tab-pane" id="home_layout" role="tabpanel">
                <p>Aquí puedes definir en formato JSON el orden de los componentes de la pantalla de inicio (Banners, Carrusel de Promociones, Categorías, etc.)</p>
                <div class="form-group">
                  <label for="home_layout_json">Estructura JSON (Home Layout)</label>
                  <textarea class="form-control" id="home_layout_json" name="home_layout_json" rows="10">{{ $configs['home_layout_json'] ?? "[\n  {\n    \"type\": \"banner_slider\"\n  },\n  {\n    \"type\": \"category_carousel\",\n    \"title\": \"Categorías Destacadas\"\n  },\n  {\n    \"type\": \"product_grid\",\n    \"title\": \"Últimos Productos\"\n  }\n]" }}</textarea>
                </div>
                <small class="text-muted">Pronto haremos un constructor visual (Drag & Drop) para esta sección.</small>
              </div>

              <!-- Tab 3: Social -->
              <div class="tab-pane" id="social" role="tabpanel">
                <div class="form-group row">
                  <label for="whatsapp_number" class="col-sm-2 col-form-label">Número de WhatsApp</label>
                  <div class="col-sm-10">
                    <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number" value="{{ $configs['whatsapp_number'] ?? '' }}">
                  </div>
                </div>
                <div class="form-group row">
                  <label for="instagram_url" class="col-sm-2 col-form-label">URL de Instagram</label>
                  <div class="col-sm-10">
                    <input type="text" class="form-control" id="instagram_url" name="instagram_url" value="{{ $configs['instagram_url'] ?? '' }}">
                  </div>
                </div>
              </div>
              
            </div>

            <div class="mt-3">
              <button type="submit" class="btn btn-primary">Guardar Configuración</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
