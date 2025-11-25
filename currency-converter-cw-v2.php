<?php
/**
 * Plugin Name: Conversor de Monedas CW
 * Description: Versión 1: Gestión dinámica de monedas en una sola pantalla, banderas, fechas y conversión usando moneda base. Interfaz en español.
 * Version: 1.0
 * Author: Jonas Cueva
 * Text Domain: cw-currency
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_menu', 'cw_admin_menu');
function cw_admin_menu(){
    add_menu_page('Tasas de Monedas CW', 'Tasas de Monedas CW', 'edit_posts', 'cw-monedas', 'cw_monedas_page', 'dashicons-money', 26);
    add_submenu_page('cw-monedas','Ajustes Conversor','Ajustes','edit_posts','cw-monedas-settings','cw_monedas_settings_page');
}

add_action('admin_enqueue_scripts', 'cw_admin_assets');
function cw_admin_assets($hook){
    if(strpos($hook, 'cw-monedas') === false) return;
    wp_enqueue_media();
    wp_enqueue_script('cw-admin-js', plugins_url('js/cw-admin.js', __FILE__), array('jquery'), false, true);
    wp_enqueue_style('cw-admin-css', plugins_url('css/cw-admin.css', __FILE__));
    wp_localize_script('cw-admin-js','cwAdmin', array(
        'nonce' => wp_create_nonce('cw_admin_nonce'),
        'confirm_delete' => '¿Eliminar esta moneda?'
    ));
}

function cw_monedas_page(){
    if(!current_user_can('edit_posts')) return;
    if(isset($_POST['cw_save_monedas'])){
        check_admin_referer('cw_save_monedas_action','cw_save_monedas_field');
        $monedas = array();
        if(isset($_POST['moneda']) && is_array($_POST['moneda'])){
            foreach($_POST['moneda'] as $i => $m){
                $mon = array(
                    'nombre' => sanitize_text_field($m['nombre']),
                    'codigo' => sanitize_text_field($m['codigo']),
                    'simbolo' => sanitize_text_field($m['simbolo']),
                    'bandera' => esc_url_raw($m['bandera']),
                    'compra' => floatval($m['compra']),
                    'venta' => floatval($m['venta']),
                    'updated' => sanitize_text_field($m['updated']),
                    'slug' => sanitize_title($m['codigo'] ? $m['codigo'] : $m['nombre'])
                );
                $monedas[] = $mon;
            }
        }
        update_option('cw_monedas', $monedas);
        echo '<div class="updated"><p>Monedas guardadas.</p></div>';
    }

    $monedas = get_option('cw_monedas', array());
    ?>
    <div class="wrap cw-wrap">
        <h1>Gestionar Monedas - CW</h1>
        <form method="post">
            <?php wp_nonce_field('cw_save_monedas_action','cw_save_monedas_field'); ?>
            <div id="cw_monedas_list">
                <?php
                if(empty($monedas)){
                    $monedas = array(
                        array('nombre'=>'Dólar Americano','codigo'=>'USD','simbolo'=>'$','bandera'=>plugins_url('assets/images/6edc9437-ab30-4a8d-97c6-62671165eea2.png', __FILE__),'compra'=>64.10,'venta'=>0,'updated'=>date('Y-m-d H:i')),
                        array('nombre'=>'Euro','codigo'=>'EUR','simbolo'=>'€','bandera'=>plugins_url('assets/images/c44a86eb-d60f-4a49-a617-970d1f73096c.png', __FILE__),'compra'=>73.80,'venta'=>75.80,'updated'=>date('Y-m-d H:i')),
                    );
                }
                foreach($monedas as $i => $m):
                ?>
                <div class="cw-moneda-row" data-index="<?php echo $i;?>">
                    <div class="cw-row-actions"><a href="#" class="cw-delete-row">Eliminar</a></div>
                    <table class="form-table">
                        <tr>
                            <th>Nombre</th>
                            <td><input type="text" name="moneda[<?php echo $i;?>][nombre]" value="<?php echo esc_attr($m['nombre']);?>" /></td>
                            <th>Código</th>
                            <td><input type="text" name="moneda[<?php echo $i;?>][codigo]" value="<?php echo esc_attr($m['codigo']);?>" /></td>
                        </tr>
                        <tr>
                            <th>Símbolo</th>
                            <td><input type="text" name="moneda[<?php echo $i;?>][simbolo]" value="<?php echo esc_attr($m['simbolo']);?>" /></td>
                            <th>Bandera</th>
                            <td>
                                <input type="text" class="cw-flag-url" name="moneda[<?php echo $i;?>][bandera]" value="<?php echo esc_attr($m['bandera']);?>" />
                                <button class="button cw-upload-flag">Seleccionar</button>
                                <div class="cw-flag-preview"><?php if(!empty($m['bandera'])) echo '<img src="'.esc_url($m['bandera']).'" style="height:20px;">';?></div>
                            </td>
                        </tr>
                        <tr>
                            <th>Tasa de Compra</th>
                            <td><input type="number" step="0.0001" name="moneda[<?php echo $i;?>][compra]" value="<?php echo esc_attr($m['compra']);?>" /></td>
                            <th>Tasa de Venta</th>
                            <td><input type="number" step="0.0001" name="moneda[<?php echo $i;?>][venta]" value="<?php echo esc_attr($m['venta']);?>" /></td>
                        </tr>
                        <tr>
                            <th>Fecha de Actualización</th>
                            <td colspan="3"><input type="datetime-local" name="moneda[<?php echo $i;?>][updated]" value="<?php echo esc_attr(date('Y-m-d\\TH:i', strtotime($m['updated'])));?>" /></td>
                        </tr>
                    </table>
                </div>
                <?php endforeach; ?>
            </div>

            <p><a href="#" id="cw_add_moneda" class="button">+ Agregar moneda</a></p>
            <p><input type="submit" name="cw_save_monedas" class="button button-primary" value="Guardar monedas" /></p>
        </form>
    </div>

    <div id="cw_moneda_template" style="display:none;">
        <div class="cw-moneda-row" data-index="__index__">
            <div class="cw-row-actions"><a href="#" class="cw-delete-row">Eliminar</a></div>
            <table class="form-table">
                <tr>
                    <th>Nombre</th><td><input type="text" name="moneda[__index__][nombre]" value="" /></td>
                    <th>Código</th><td><input type="text" name="moneda[__index__][codigo]" value="" /></td>
                </tr>
                <tr>
                    <th>Símbolo</th><td><input type="text" name="moneda[__index__][simbolo]" value="" /></td>
                    <th>Bandera</th>
                    <td>
                        <input type="text" class="cw-flag-url" name="moneda[__index__][bandera]" value="" />
                        <button class="button cw-upload-flag">Seleccionar</button>
                        <div class="cw-flag-preview"></div>
                    </td>
                </tr>
                <tr>
                    <th>Tasa de Compra</th><td><input type="number" step="0.0001" name="moneda[__index__][compra]" value="" /></td>
                    <th>Tasa de Venta</th><td><input type="number" step="0.0001" name="moneda[__index__][venta]" value="" /></td>
                </tr>
                <tr>
                    <th>Fecha de Actualización</th>
                    <td colspan="3"><input type="datetime-local" name="moneda[__index__][updated]" value="<?php echo date('Y-m-d\\TH:i');?>" /></td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}

/* Ajustes base */
function cw_monedas_settings_page(){
    if(!current_user_can('edit_posts')) return;
    if(isset($_POST['cw_save_settings'])){
        check_admin_referer('cw_save_settings_action','cw_save_settings_field');
        update_option('cw_base_currency_code', sanitize_text_field($_POST['cw_base_currency_code']));
        update_option('cw_base_currency_value', floatval($_POST['cw_base_currency_value']));
        echo '<div class="updated"><p>Ajustes guardados.</p></div>';
    }
    $base = get_option('cw_base_currency_code','');
    $base_value = get_option('cw_base_currency_value', 1);
    $mons = get_option('cw_monedas', array());
    ?>
    <div class="wrap">
        <h1>Ajustes - Conversor CW</h1>
        <form method="post">
            <?php wp_nonce_field('cw_save_settings_action','cw_save_settings_field'); ?>
            <table class="form-table">
                <tr>
                    <th>Moneda base (código)</th>
                    <td>
                        <!-- Cambio de input a select para elegir código y valor de forma integrada -->
                        <select name="cw_base_currency_code">
                            <option value="">-- Selecciona una moneda --</option>
                            <?php foreach($mons as $m): ?>
                                <option value="<?php echo esc_attr($m['codigo']); ?>" <?php selected($base, $m['codigo']); ?>>
                                    <?php echo esc_html($m['nombre']); ?> (<?php echo esc_html($m['codigo']); ?>) - Tasa: <?php echo esc_html($m['compra']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Selecciona la moneda base. Se usará su tasa de compra como referencia.</p>
                    </td>
                </tr>
                <tr>
                    <th>Valor de la moneda base</th>
                    <td><input type="number" step="0.0001" name="cw_base_currency_value" value="<?php echo esc_attr($base_value);?>" />
                    <p class="description">Valor o tasa de la moneda base para conversiones (por defecto: 1).</p></td>
                </tr>
            </table>
            <p><input type="submit" name="cw_save_settings" class="button button-primary" value="Guardar ajustes" /></p>
        </form>
    </div>
    <?php
}

/* Frontend assets */
add_action('wp_enqueue_scripts','cw_frontend_assets');
function cw_frontend_assets(){
    wp_enqueue_style('cw-main-style', plugins_url('css/cw-style.css', __FILE__));
    wp_enqueue_script('cw-main-js', plugins_url('js/cw-main.js', __FILE__), array('jquery'), false, true);
    wp_localize_script('cw-main-js', 'cw_ajax_obj', array('ajax_url'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('cw_ajax_nonce')));
}

/* AJAX */
add_action('wp_ajax_cw_convert', 'cw_ajax_convert');
add_action('wp_ajax_nopriv_cw_convert', 'cw_ajax_convert');

function cw_get_monedas_indexed(){
    $mons = get_option('cw_monedas', array());
    $indexed = array();
    foreach($mons as $m){
        $code = strtoupper($m['codigo']);
        $indexed[$code] = $m;
    }
    return $indexed;
}

function cw_ajax_convert(){
    check_ajax_referer('cw_ajax_nonce','nonce');
    $from = sanitize_text_field($_POST['from']);
    $to = sanitize_text_field($_POST['to']);
    $amount = floatval($_POST['amount']);
    if(!$from || !$to || $amount <= 0){ wp_send_json_error('Parámetros inválidos'); }
    $mons = cw_get_monedas_indexed();
    $from_u = strtoupper($from);
    $to_u = strtoupper($to);
    if(!isset($mons[$from_u]) || !isset($mons[$to_u])){ wp_send_json_error('Moneda no encontrada'); }
    $from_mon = $mons[$from_u];
    $to_mon = $mons[$to_u];
    $base_code = get_option('cw_base_currency_code','');
    if(!$base_code){ $keys = array_keys($mons); $base_code = $keys[0]; }
    $base_code = strtoupper($base_code);
    if(!isset($mons[$base_code])){ $base_code = array_keys($mons)[0]; }
    // conversion via base
    $from_sell = floatval($from_mon['venta']);
    $to_buy = floatval($to_mon['compra']);
    if($from_sell <= 0 || $to_buy <= 0){ wp_send_json_error('Tasas inválidas para conversión'); }
    $base_amount = $amount * $from_sell;
    $converted = $base_amount / $to_buy;
    wp_send_json_success(array('converted'=>round($converted,4),'base_amount'=>round($base_amount,4),'from'=>$from_mon['nombre'],'to'=>$to_mon['nombre'],'base_code'=>$base_code));
}

/* Widgets */
class CW_Converter_Widget extends WP_Widget {
    public function __construct(){ parent::__construct('cw_converter_widget','CW - Conversor de Monedas'); }
    public function widget($args,$instance){
        echo $args['before_widget'];
        $mons = get_option('cw_monedas', array());
        if(empty($mons)){ echo '<p>No hay monedas configuradas.</p>'; echo $args['after_widget']; return;}
        echo '<div class="cw-converter"><label>Cantidad</label><input type="number" id="cw_amount" value="1" min="0.0001" step="any" />';
        echo '<div class="cw-row"><div><label>Desde</label><select id="cw_from">';
        foreach($mons as $m) echo '<option value="'.esc_attr($m['codigo']).'">'.esc_html($m['nombre']).' ('.esc_html($m['codigo']).')</option>';
        echo '</select></div><div><label>Hacia</label><select id="cw_to">';
        foreach($mons as $index => $m) {
            $selected = ($index === 1) ? ' selected' : '';
            echo '<option value="'.esc_attr($m['codigo']).'"'.$selected.'>'.esc_html($m['nombre']).' ('.esc_html($m['codigo']).')</option>';
        }
        echo '</select></div></div>';
        echo '<div id="cw_result" style="margin-top:12px;">&nbsp;</div>';
        echo '<button id="cw_convert_btn" class="button">Convertir</button>';
        echo '</div>';
        echo $args['after_widget'];
    }
    public function form($instance){}
    public function update($new,$old){ return $old; }
}
add_action('widgets_init', function(){ register_widget('CW_Converter_Widget'); });

class CW_Table_Widget extends WP_Widget {
    public function __construct(){ parent::__construct('cw_table_widget','CW - Tabla de Tasas'); }
    public function widget($args,$instance){
        echo $args['before_widget'];
        $mons = get_option('cw_monedas', array());
        if(empty($mons)){ echo '<p>No hay monedas configuradas.</p>'; echo $args['after_widget']; return;}
        $base_code = strtoupper(get_option('cw_base_currency_code',''));
        $base_name = '';
        $base_symbol = '';
        $base_flag = '';
        
        $base_mon = null;
        foreach($mons as $m){
            if(strtoupper($m['codigo']) === $base_code){
                $base_mon = $m;
                break;
            }
        }
        
        echo '<div class="cw-table-widget"><div class="cw-table-head"><div>Moneda</div><div>Tasa de Compra</div><div>Tasa de Venta</div><div>Fecha de Actualización</div></div><div class="cw-table-body">';
        
        if($base_mon){
            echo '<div class="cw-table-row cw-base-currency-row"><div class="cw-col-moneda">';
            if(!empty($base_mon['bandera'])) echo '<img src="'.esc_url($base_mon['bandera']).'" style="height:18px;margin-right:8px;vertical-align:middle;">';
            echo esc_html($base_mon['nombre']).' ('.esc_html($base_mon['codigo']).')</div>';
            echo '<div>-</div><div>-</div><div>'.cw_format_date($base_mon['updated']).'</div></div>';
        }
        
        foreach($mons as $m){
            if($base_code && strtoupper($m['codigo']) === $base_code) continue;
            
            echo '<div class="cw-table-row"><div class="cw-col-moneda">';
            if(!empty($m['bandera'])) echo '<img src="'.esc_url($m['bandera']).'" style="height:18px;margin-right:8px;vertical-align:middle;">';
            echo esc_html($m['nombre']).' ('.esc_html($m['codigo']).')</div>';
            echo '<div>'.esc_html(number_format(floatval($m['compra']), 2, '.', '')).'</div><div>'.esc_html(number_format(floatval($m['venta']), 2, '.', '')).'</div><div>'.cw_format_date($m['updated']).'</div></div>';
        }
        echo '</div></div>';
        echo $args['after_widget'];
    }
    public function form($instance){}
    public function update($new,$old){ return $old; }
}
add_action('widgets_init', function(){ register_widget('CW_Table_Widget'); });

add_shortcode('cw_currency_converter', function($atts){ ob_start(); the_widget('CW_Converter_Widget'); return ob_get_clean(); });
add_shortcode('cw_currency_table', function($atts){ ob_start(); the_widget('CW_Table_Widget'); return ob_get_clean(); });

register_activation_hook(__FILE__,'cw_v2_create_assets');
function cw_v2_create_assets(){
    $css = plugin_dir_path(__FILE__).'css/cw-style.css';
    $admin_css = plugin_dir_path(__FILE__).'css/cw-admin.css';
    $js = plugin_dir_path(__FILE__).'js/cw-main.js';
    $admin_js = plugin_dir_path(__FILE__).'js/cw-admin.js';
    if(!file_exists($css)) file_put_contents($css, '/* estilos frontend */');
    if(!file_exists($admin_css)) file_put_contents($admin_css, '/* estilos admin */');
    if(!file_exists($js)) file_put_contents($js, '/* js frontend */');
    if(!file_exists($admin_js)) file_put_contents($admin_js, '/* js admin */');
}

// Function to format dates as "23/10/2025 1:34 pm"
function cw_format_date($date_string){
    $timestamp = strtotime($date_string);
    if($timestamp === false) return $date_string;
    return date('d/m/Y g:i a', $timestamp);
}
?>
