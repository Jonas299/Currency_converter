# Conversor de Monedas CW 💱

**Versión:** 1.0  
**Autor:** Jonas Cueva  
**Text Domain:** cw-currency

Descripción
-----------
Conversor de Monedas CW es un plugin ligero para WordPress que permite gestionar monedas personalizadas (nombre, código, símbolo, bandera, tasa de compra/venta y fecha de actualización) desde una única pantalla en el panel de administración y exponerlas en el frontend mediante widgets o shortcodes para convertir valores entre monedas.

Características
---------------
- Interfaz de administración para añadir/editar/eliminar monedas.  
- Soporta imagen de bandera por moneda.  
- Widgets integrados: conversor y tabla de tasas.  
- Shortcodes para insertar el conversor o la tabla en cualquier página/entrada.  
- Conversión vía AJAX en el frontend (segura con nonce).  
- Selección de moneda base y valor de referencia.

Requisitos
---------
- WordPress 4.5+ (recomendado versión más reciente)  
- PHP 5.6+ (se recomienda PHP 7.4+)  
- Capacidad para instalar plugins y gestionar opciones (rol con `manage_options`).

Instalación
-----------
1. Copia la carpeta del plugin al directorio `wp-content/plugins/` de tu instalación de WordPress.
2. Activa el plugin desde el panel de administración en Plugins → Plugins instalados.
3. Ve a el menú "Monedas CW" en el panel de administración para empezar a configurar.

Uso
---
Desde el panel de administración (Monedas CW):
- Agrega monedas con su nombre, código (ej. USD), símbolo, imagen de bandera y las tasas de compra/venta.  
- Guarda los cambios.  
- Opcionalmente selecciona una moneda base en Ajustes → Moneda base y su valor.

Widgets
-------
- CW - Conversor de Monedas: widget que muestra un campo cantidad, selección "Desde" y "Hacia" y un botón "Convertir". Usa AJAX para obtener el resultado.
- CW - Tabla de Tasas: widget que muestra una tabla con nombre, tasas de compra/venta y fecha de actualización.

Shortcodes
----------
- `[cw_currency_converter]` — Inserta el conversor (usa internamente el widget `CW_Converter_Widget`).
- `[cw_currency_table]` — Inserta la tabla de tasas (usa internamente el widget `CW_Table_Widget`).

Ajustes y administración
------------------------
- Menú: Monedas CW → Gestionar Monedas — Añade/edita/elimina monedas.  
- Menú: Monedas CW → Ajustes Conversor — Selecciona la moneda base (código) y el valor de referencia.
- Las imágenes de bandera se pueden subir mediante el selector de medios desde la pantalla de monedas.

API / AJAX
----------
El plugin expone una acción AJAX para conversiones:
- Acción: `cw_convert` (requiere `nonce` generado por `cw_ajax_nonce`).
- Parámetros esperados: `from` (código origen), `to` (código destino), `amount` (cantidad numérica).
- Respuesta JSON: `success` con objeto `{ converted, base_amount, from, to, base_code }` o `error` con mensaje.

Ejemplo (desde JS usando el objeto localizado `cw_ajax_obj`):
- `cw_ajax_obj.ajax_url` — URL a `admin-ajax.php`  
- `cw_ajax_obj.nonce` — nonce de seguridad

Preguntas frecuentes (FAQ)
--------------------------
P: ¿Cómo se calculan las conversiones?  
R: El plugin convierte usando las tasas almacenadas en las monedas: toma la tasa de venta de la moneda origen y la tasa de compra de la moneda destino para realizar la conversión (vía moneda base interna). Asegúrate de que las tasas sean valores positivos.

P: ¿Qué pasa si no hay monedas configuradas?  
R: Los widgets y shortcodes muestran un mensaje indicando que no hay monedas configuradas.

Desarrollo y contribuciones
---------------------------
- El archivo principal del plugin es `currency-converter-cw-v2.php`.  
- Archivos de estilos: `css/cw-style.css`, `css/cw-admin.css`.  
- Scripts: `js/cw-main.js`, `js/cw-admin.js`.  
- Imágenes de banderas: `assets/images/`.

Si deseas contribuir:
- Crea una rama a partir de `main`/`master`, realiza cambios y abre un pull request con pruebas o descripción de los cambios.  
- Para reportar errores o pedir mejoras, abre un issue incluyendo: versión de WordPress, versión de PHP y pasos para reproducir.

Changelog
---------
### 1.0
- Versión inicial: gestión de monedas, widgets, shortcodes y conversiones vía AJAX.

Licencia
--------
Este plugin está pensado para distribuirse bajo una licencia compatible con WordPress (GPLv2 o posterior). Revisa los archivos del plugin o contacta con el autor para confirmar la licencia exacta.

Archivos importantes
-------------------
- `currency-converter-cw-v2.php` — Archivo principal del plugin (cabecera, hooks, widgets, AJAX).  
- `css/` — Estilos frontend y admin.  
- `js/` — Javascript frontend y admin.  
- `assets/images/` — Banderas usadas por las monedas.

Contacto
--------
Autor: Jonas Cueva  
(La información de contacto no está incluida en el encabezado del plugin; revisa el plugin o los metadatos si deseas añadir un mail o URL de soporte.)
