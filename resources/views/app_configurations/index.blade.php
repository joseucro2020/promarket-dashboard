@extends('layouts/contentLayoutMaster')

@section('title', 'Configuración de la App')

@section('vendor-style')
<style>
  .block-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 10px;
    position: relative;
    cursor: grab;
  }
  .block-item:active {
    cursor: grabbing;
  }
  .block-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    margin-bottom: 10px;
  }
  .remove-block {
    cursor: pointer;
    color: red;
  }
  .drag-handle {
    cursor: grab;
    margin-right: 10px;
    color: #6c757d;
  }
</style>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

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

          <form action="{{ route('app-config.store') }}" method="POST" id="config-form">
            @csrf

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#branding" role="tab">1. Branding (Marca)</a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#home_layout" role="tab">2. Layout de Inicio</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#social" role="tab">3. Redes y Contacto</a>
              </li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content mt-2">
              
              <!-- Tab 1: Branding -->
              <div class="tab-pane" id="branding" role="tabpanel">
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
              <div class="tab-pane active" id="home_layout" role="tabpanel">
                <p>Arrastra y suelta los bloques para construir la pantalla de inicio de la aplicación.</p>
                
                <div class="mb-2 d-flex align-items-center">
                    <select id="block-selector" class="form-control" style="max-width: 300px; margin-right: 10px;">
                        <option value="HeroBanners">Hero Banners (Slider)</option>
                        <option value="TopCategories">Top Categories (Grilla)</option>
                        <option value="BestSellersCarousel">Lo más vendido (Carrusel)</option>
                        <option value="SingleSpecialCategory">Categoría Especial</option>
                        <option value="SpecialCategoriesCarousel">Carrusel de Categorías</option>
                        <option value="TextWidget">Texto / Título</option>
                        <option value="ExclusiveOffersWidget">Ofertas Exclusivas</option>
                        <option value="ImageBannerWidget">Banner de Imagen con Enlace</option>
                        <option value="InfoCarousel">Carrusel de Imágenes (Múltiples)</option>
                    </select>
                    <button type="button" class="btn btn-outline-primary" id="btn-add-block">
                        <i data-feather="plus"></i> Añadir Bloque
                    </button>
                </div>

                <!-- Contenedor Drag and Drop -->
                <div id="layout-builder" style="min-height: 200px; padding: 10px; border: 2px dashed #ccc;">
                  <!-- Bloques dinámicos aquí -->
                </div>

                <!-- Input oculto para guardar el JSON final -->
                <input type="hidden" id="home_layout_json" name="home_layout_json" value="">
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
              <button type="button" id="btn-save-config" class="btn btn-primary">Guardar Configuración</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Templates HTML para los bloques (Ocultos) -->
<template id="tpl-generic">
    <div class="block-item" data-type="##TYPE##">
        <div class="block-header" style="align-items: flex-start;">
            <div>
                <span><i data-feather="menu" class="drag-handle"></i> <strong>##TYPE##</strong></span>
                <p class="text-muted mb-0 mt-50" style="font-size: 0.85rem; padding-left: 24px; font-weight: normal; margin-top: 5px;">##DESCRIPTION##</p>
            </div>
            <span class="remove-block" title="Eliminar"><i data-feather="trash-2"></i></span>
        </div>
    </div>
</template>

<template id="tpl-SingleSpecialCategory">
    <div class="block-item" data-type="SingleSpecialCategory">
        <div class="block-header">
            <span><i data-feather="menu" class="drag-handle"></i> Categoría Especial</span>
            <span class="remove-block"><i data-feather="trash-2"></i></span>
        </div>
        <div class="row">
            <div class="col-md-4 form-group">
                <label>Selecciona una Categoría:</label>
                <select class="form-control select2-init category-id">
                    <option value="">Buscar y seleccionar...</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Requerido.</small>
            </div>
            <div class="col-md-4 form-group">
                <label>Título Personalizado (Opcional):</label>
                <input type="text" class="form-control title-val" value="" placeholder="Si se deja vacío, usa el nombre original">
            </div>
            <div class="col-md-4 form-group">
                <label>Descripción (Opcional):</label>
                <input type="text" class="form-control desc-val" value="" placeholder="Ej: Las mejores ofertas en...">
            </div>
        </div>
    </div>
</template>

<template id="tpl-TextWidget">
    <div class="block-item" data-type="TextWidget">
        <div class="block-header">
            <span><i data-feather="menu" class="drag-handle"></i> Texto Destacado</span>
            <span class="remove-block"><i data-feather="trash-2"></i></span>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Texto:</label>
                <textarea class="form-control text-val" rows="2" placeholder="Ej: BUILT FOR SPEED."></textarea>
            </div>
            <div class="col-md-2 form-group">
                <label>Color (Hex):</label>
                <input type="color" class="form-control color-val" value="#111111">
            </div>
            <div class="col-md-2 form-group">
                <label>Alineación:</label>
                <select class="form-control align-val">
                    <option value="left">Izquierda</option>
                    <option value="center">Centro</option>
                    <option value="right">Derecha</option>
                </select>
            </div>
            <div class="col-md-2 form-group">
                <label>Tamaño Fuente:</label>
                <select class="form-control font-size-val">
                    <option value="12px">12px (Pequeño)</option>
                    <option value="14px">14px</option>
                    <option value="16px">16px (Normal)</option>
                    <option value="18px">18px</option>
                    <option value="20px">20px (Grande)</option>
                    <option value="24px">24px (Muy Grande)</option>
                    <option value="28px">28px</option>
                    <option value="32px">32px (Título)</option>
                </select>
            </div>
        </div>
        <div class="row mt-1">
            <div class="col-md-4 form-group">
                <label>Grosor (Font Weight):</label>
                <select class="form-control font-weight-val">
                    <option value="normal">Normal (400)</option>
                    <option value="bold">Bold (700)</option>
                    <option value="900">Black (900)</option>
                </select>
            </div>
            <div class="col-md-4 form-group">
                <label>Estilo (Font Style):</label>
                <select class="form-control font-style-val">
                    <option value="normal">Normal</option>
                    <option value="italic">Cursiva (Italic)</option>
                </select>
            </div>
            <div class="col-md-4 form-group">
                <label>Relleno (Padding):</label>
                <input type="text" class="form-control padding-val" value="" placeholder="Ej: 24px 16px">
            </div>
        </div>
    </div>
</template>

<template id="tpl-ImageBannerWidget">
    <div class="block-item" data-type="ImageBannerWidget">
        <div class="block-header">
            <span><i data-feather="menu" class="drag-handle"></i> Banner de Imagen</span>
            <span class="remove-block"><i data-feather="trash-2"></i></span>
        </div>
        <div class="row">
            <div class="col-md-6 form-group">
                <label>Opción 1: Subir una imagen desde tu PC</label>
                <input type="file" class="form-control image-file" accept="image/*">
                <small class="text-muted" style="display:block; margin-top:4px;">
                    <i data-feather="info" style="width:14px; height:14px;"></i> Tamaño recomendado: <strong>800x400 px</strong> (Proporción 2:1).
                </small>
                
                <label class="mt-2">Opción 2: Pegar URL (si ya está subida)</label>
                <input type="text" class="form-control image-url-hidden" value="" placeholder="https://...">
            </div>
            <div class="col-md-6 form-group">
                <label>URL de Destino al Tocar (Opcional):</label>
                <input type="text" class="form-control link-url" value="" placeholder="Ej: /categories/ofertas">
                
                <div class="mt-1 text-center" style="background:#eee; padding:5px; border-radius:8px; min-height: 50px; display:flex; align-items:center; justify-content:center;">
                    <img src="" class="img-preview" style="max-height:80px; display:none; border-radius:4px;" />
                    <span class="text-muted no-img-text" style="font-size:12px;">Sin imagen</span>
                </div>
            </div>
        </div>
        </div>
    </div>
</template>

<template id="tpl-InfoCarousel">
    <div class="block-item" data-type="InfoCarousel">
        <div class="block-header">
            <span><i data-feather="menu" class="drag-handle"></i> Carrusel de Imágenes</span>
            <span class="remove-block"><i data-feather="trash-2"></i></span>
        </div>
        <small class="text-muted mb-2" style="display:block;">Agrega múltiples imágenes para crear un carrusel deslizable. Proporción recomendada: 2:1 (ej. 800x400 px).</small>
        <div class="carousel-items-container">
            <!-- Items injected here -->
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary btn-add-carousel-image mt-2">
            <i data-feather="plus" style="width:14px; height:14px;"></i> Añadir Imagen
        </button>
    </div>
</template>

<template id="tpl-carousel-image-item">
    <div class="row carousel-image-item mt-2 align-items-center" style="border-left: 3px solid #005ce6; padding-left: 10px; margin-left: 0; margin-right:0;">
        <div class="col-md-5 form-group mb-0">
            <label style="font-size:12px;">Subir Imagen</label>
            <input type="file" class="form-control image-file" accept="image/*" style="font-size:12px; padding:4px;">
        </div>
        <div class="col-md-5 form-group mb-0">
            <label style="font-size:12px;">O Pegar URL</label>
            <input type="text" class="form-control image-url-hidden" value="" placeholder="https://..." style="font-size:12px;">
        </div>
        <div class="col-md-2 form-group mb-0 text-right">
            <button type="button" class="btn btn-danger btn-sm btn-remove-carousel-image" style="padding: 4px 8px;"><i data-feather="trash-2" style="width:14px; height:14px;"></i></button>
        </div>
    </div>
</template>

<template id="tpl-BestSellersCarousel">
    <div class="block-item" data-type="BestSellersCarousel">
        <div class="block-header">
            <span><i data-feather="menu" class="drag-handle"></i> Lo más vendido (Carrusel)</span>
            <span class="remove-block"><i data-feather="trash-2"></i></span>
        </div>
        <div class="row">
            <div class="col-md-4 form-group">
                <label>Título:</label>
                <input type="text" class="form-control title-val" value="" placeholder="Ej: LOS MÁS VENDIDOS">
            </div>
            <div class="col-md-8 form-group">
                <label>Descripción (Subtítulo):</label>
                <input type="text" class="form-control desc-val" value="" placeholder="Ej: Productos populares comprados por otros clientes frecuentes.">
            </div>
        </div>
    </div>
</template>

@endsection

@section('page-script')
<!-- Sortable JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    
    // Configuración Inicial guardada en la Base de Datos
    const initialLayout = {!! json_encode($configs['home_layout_json'] ?? []) !!};
    
    // Contenedor principal
    const builder = document.getElementById('layout-builder');
    
    // Inicializar Drag and Drop con Sortable
    new Sortable(builder, {
        animation: 150,
        handle: '.drag-handle',
        ghostClass: 'bg-light'
    });

    // Función para renderizar los iconos de Feather (los basureros y handles)
    function reloadIcons() {
        if(feather) { feather.replace(); }
    }

    // Agregar un bloque al HTML
    function appendBlock(data) {
        let type = data.type;
        let $block = null;

        if (type === 'SingleSpecialCategory') {
            $block = $($('#tpl-SingleSpecialCategory').html());
            if (data.data) {
                $block.find('.category-id').val(data.data.id || '');
                $block.find('.title-val').val(data.data.title || '');
                $block.find('.desc-val').val(data.data.description || '');
            }
            // Initialize Select2 after appending to DOM
            setTimeout(() => {
                $block.find('.select2-init').select2({
                    width: '100%',
                    placeholder: "Buscar y seleccionar..."
                });
            }, 100);
        } else if (type === 'TextWidget') {
            $block = $($('#tpl-TextWidget').html());
            if (data.data) {
                $block.find('.text-val').val(data.data.text || '');
                $block.find('.color-val').val(data.data.color || '#111111');
                $block.find('.align-val').val(data.data.align || 'center');
                $block.find('.font-size-val').val(data.data.fontSize || '24px');
                $block.find('.font-weight-val').val(data.data.fontWeight || '900');
                $block.find('.font-style-val').val(data.data.fontStyle || 'italic');
                $block.find('.padding-val').val(data.data.padding || '24px 16px');
            }
        } else if (type === 'ImageBannerWidget') {
            $block = $($('#tpl-ImageBannerWidget').html());
            if (data.data) {
                let imgUrl = data.data.imageUrl || '';
                $block.find('.image-url-hidden').val(imgUrl);
                $block.find('.link-url').val(data.data.linkUrl || '');
                if (imgUrl) {
                    $block.find('.img-preview').attr('src', imgUrl).show();
                    $block.find('.no-img-text').hide();
                }
            }
            // Evento para previsualizar al elegir archivo
            $block.find('.image-file').on('change', function(e) {
                if(e.target.files && e.target.files[0]) {
                    let reader = new FileReader();
                    let preview = $(this).closest('.row').find('.img-preview');
                    let noImg = $(this).closest('.row').find('.no-img-text');
                    reader.onload = function(ev) {
                        preview.attr('src', ev.target.result).show();
                        noImg.hide();
                    }
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        } else if (type === 'BestSellersCarousel') {
            $block = $($('#tpl-BestSellersCarousel').html());
            if (data.data) {
                $block.find('.title-val').val(data.data.title || '');
                $block.find('.desc-val').val(data.data.description || '');
            }
        } else if (type === 'InfoCarousel') {
            $block = $($('#tpl-InfoCarousel').html());
            if (data.data && Array.isArray(data.data)) {
                data.data.forEach(item => {
                    let $item = $($('#tpl-carousel-image-item').html());
                    $item.find('.image-url-hidden').val(item.image || '');
                    $block.find('.carousel-items-container').append($item);
                });
            }
        } else {
            let tpl = $('#tpl-generic').html();
            let desc = '';
            
            if (type === 'TopCategories') {
                desc = 'Muestra una cuadrícula interactiva con las categorías principales de la tienda.';
            } else if (type === 'HeroBanners') {
                desc = 'Muestra el carrusel principal de imágenes promocionales (banners) en la parte superior.';
            }

            tpl = tpl.replace(/##TYPE##/g, type);
            tpl = tpl.replace('##DESCRIPTION##', desc);
            $block = $(tpl);
        }

        $('#layout-builder').append($block);
        reloadIcons();
    }

    // Renderizar estado inicial
    if (Array.isArray(initialLayout) && initialLayout.length > 0) {
        initialLayout.forEach(block => {
            appendBlock(block);
        });
    }

    // Evento de Añadir Bloque manual
    $('#btn-add-block').on('click', function() {
        const type = $('#block-selector').val();
        appendBlock({ type: type });
    });

    // Evento de Eliminar Bloque
    $(document).on('click', '.remove-block', function() {
        $(this).closest('.block-item').remove();
    });

    // Add Carousel Image Button
    $(document).on('click', '.btn-add-carousel-image', function() {
        let container = $(this).siblings('.carousel-items-container');
        let item = $($('#tpl-carousel-image-item').html());
        container.append(item);
        feather.replace();
    });

    // Remove Carousel Image Button
    $(document).on('click', '.btn-remove-carousel-image', function() {
        $(this).closest('.carousel-image-item').remove();
    });

    // Evento de Guardar (Construye el JSON y hace Submit)
    $('#btn-save-config').on('click', async function(e) {
        e.preventDefault();
        
        let btn = $(this);
        let originalText = btn.text();
        btn.prop('disabled', true).text('Guardando y subiendo imágenes...');

        let uploadPromises = [];

        $('.image-file').each(function() {
            let fileInput = this;
            let container = $(this).closest('.form-group').parent(); // Finds the row containing both inputs
            
            if (fileInput && fileInput.files && fileInput.files[0]) {
                let formData = new FormData();
                formData.append('image', fileInput.files[0]);
                formData.append('_token', '{{ csrf_token() }}');
                
                let p = $.ajax({
                    url: '{{ route("app-config.upload-image") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                }).then(res => {
                    if (res.success) {
                        container.find('.image-url-hidden').val(res.url); // Actualizamos la URL con la recién subida
                    }
                }).catch(err => {
                    console.error("Error al subir imagen", err);
                });
                
                uploadPromises.push(p);
            }
        });

        // Esperar a que terminen todas las subidas de imágenes
        if (uploadPromises.length > 0) {
            await Promise.all(uploadPromises);
        }

        let finalLayout = [];
        
        $('.block-item').each(function() {
            let el = $(this);
            let type = el.data('type');
            let blockData = { type: type };

            if (type === 'SingleSpecialCategory') {
                blockData.data = {
                    id: el.find('.category-id').val(),
                    title: el.find('.title-val').val(),
                    description: el.find('.desc-val').val()
                };
            } else if (type === 'TextWidget') {
                blockData.data = {
                    text: el.find('.text-val').val(),
                    color: el.find('.color-val').val(),
                    align: el.find('.align-val').val(),
                    fontSize: el.find('.font-size-val').val() || '24px',
                    fontWeight: el.find('.font-weight-val').val(),
                    fontStyle: el.find('.font-style-val').val(),
                    padding: el.find('.padding-val').val() || '24px 16px'
                };
            } else if (type === 'ImageBannerWidget') {
                blockData.data = {
                    imageUrl: el.find('.image-url-hidden').val(),
                    linkUrl: el.find('.link-url').val()
                };
            } else if (type === 'BestSellersCarousel') {
                blockData.data = {
                    title: el.find('.title-val').val(),
                    description: el.find('.desc-val').val()
                };
            } else if (type === 'InfoCarousel') {
                let images = [];
                el.find('.carousel-image-item').each(function() {
                    let url = $(this).find('.image-url-hidden').val();
                    if (url) {
                        images.push({ image: url });
                    }
                });
                blockData.data = images;
            }
            
            finalLayout.push(blockData);
        });

        // Escribimos el string de JSON en el input oculto
        $('#home_layout_json').val(JSON.stringify(finalLayout, null, 2));

        // Enviamos el formulario
        $('#config-form').submit();
    });
});
</script>
@endsection
