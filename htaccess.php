<?php
/**
* Plugin Name: htaccess Sources
* Plugin URI: http://htaccesis.com
* Description: This plugin simulate htaccess
* Version: 1.0.0
* Author: Luis David del Barrio Gonzalez
* Author URI: http://alejandro.im
*/

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'htaccess_WP' ) ) {
class htaccess_WP {
    public function __construct() {
        add_action( 'admin_menu',array($this, 'htaccessAdminMenu'));
        add_action( 'admin_footer', array($this,'sendAjaxRequest' ));
        add_action('wp_ajax_GetAjaxRequest', array($this, 'GetAjaxRequest'));
    }

    public function htaccessAdminMenu()
    {
        add_menu_page ( 'htaccess Edit', 'htaccess Edit', 'read',  'htaccess-edit', array($this,'htaccessAdminPage'), "dashicons-editor-code", 80);
    }

    public function htaccessAdminPage()
    {
        wp_register_style( 'style_css', WP_PLUGIN_URL. '/htaccess-sources/assets/css/style.css', false, '1.0.0' );
        wp_enqueue_style( 'style_css' );
        wp_enqueue_script( 'drag', plugins_url( 'assets/js/drag.js', __FILE__ ) );
        wp_enqueue_script( 'jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js');

    ?>

        <div class="wrap">
            <h1>Make a visual edit of htaccess</h1>
            <form>
                <h3>Introducir codigo htaccess</h3>
                <?= wp_editor('', 'htaccess_txt') ?>
            </form>

            <?php $elements = file(dirname( __FILE__ ) . '/includes/suggest.txt');?>
            <ul id="draganddrop" class="elements">
                <h3>Sugerencias</h3>
                <?php foreach($elements as $element):
                    $element = explode('||TOOLTIP||',$element);
                    ?>
                    <div class="tooltip">
                        <li id="<?= $element[0] ?>" class="element" style="background:<?= sprintf('#%06X', mt_rand(0, 0xFFFFFF));?>"><?= str_replace('{DOMAIN}', get_site_url(), $element[0]) ?></li>
                        <span class="tooltiptext"><?= $element[1] ?> </span>
                    </div>

                <?php endforeach; ?>
            </ul>
            <div class="buttons">
                <h3>Opciones</h3>
                <a id="test">Test</a>
                <a id="save">Guardar</a>
            </div>
            <div class="results">
                <h3>Resultado de la consulta</h3>
            </div>
        </div>
    <?php
    }

    public function sendAjaxRequest() {
        ?>
        <script>
            jQuery(document).ready( function() {
                jQuery('#test').click(function() {
                    var text = jQuery('#htaccess_txt').val();
                    var data = {
                        'action': 'GetAjaxRequest',
                        'htacess': text
                    };

                    jQuery.get(ajaxurl, data, function(response) {
                       var result = jQuery.parseJSON(response);
                        console.log(response);

                        jQuery( ".results" ).append('<p>' + result[0] + '</p>');
                        jQuery( ".results" ).append('<p>' + result[3] + '</p>');
                    });
                });
            });
        </script>
    <?php
    }

    public function GetAjaxRequest() {
        $txt = $_GET['htacess'];

        $htacces_file = fopen(dirname( __FILE__ ) . "/includes/envelop/.htaccess", "w");
        $txt = $this->str_replace_first('/', '/wp-content/plugins/htaccess-sources/includes/envelop/',$txt);
        fwrite($htacces_file, $txt);
        fclose($htacces_file);
        $url = get_site_url() . '/wp-content/plugins/htaccess-sources/includes/envelop/';

        print_r(json_encode(get_headers($url)));

        exit();
    }

    function str_replace_first($search, $replace, $subject) {

        $pos = strpos($subject, $search);
        if ($pos !== false) {
            return substr_replace($subject, $replace, $pos, strlen($search));
        }
        return $subject;
    }


    }
$GLOBALS['htaccess_WP'] = new htaccess_WP();
}
?>
