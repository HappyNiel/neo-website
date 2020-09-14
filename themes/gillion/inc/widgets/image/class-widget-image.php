<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Direct access forbidden.' ); }

class Widget_Image extends WP_Widget {

    /**
     * Widget constructor.
     */
    private $options;
    private $prefix;
    function __construct() {

        $widget_ops = array( 'description' => esc_html__( 'Add your image', 'gillion' ) );
        parent::__construct( false, esc_html__( 'Shufflehound Image', 'gillion' ), $widget_ops );
        $this->options = array(

            'id' => array( 'type' => 'unique' ),

            'title' => array(
                'type' => 'text',
                'label' => esc_html__('Widget Title', 'gillion'),
                'value' => esc_html__('Image', 'gillion'),
            ),


            'image' => array(
                'label'   => esc_html__( 'Image', 'gillion' ),
                'desc'    => esc_html__( 'Upload image', 'gillion' ),
                'type'    => 'upload',
                'images_only' => true,
            ),

            'url'   => array(
                'type'  => 'text',
                'label' => esc_html__( 'URL', 'gillion' ),
                'desc'  => esc_html__( 'Enter URL', 'gillion' ),
            ),

            'blackandwhite' => array(
                'label' => esc_html__( 'Black and White Image', 'gillion' ),
                'desc' => esc_html__( 'Set image black and white (when not hovered)', 'gillion' ),
                'type'  => 'switch',
                'value' => false,
                'left-choice' => array(
                    'value' => false,
                    'label' => esc_html__('Off', 'gillion'),
                ),
                'right-choice' => array(
                    'value' => true,
                    'label' => esc_html__('On', 'gillion'),
                ),
            ),

        );
        $this->prefix = 'online_support';
    }

    function widget( $args, $instance ) {
        extract( $args );
        $params = array();

        foreach ( $instance as $key => $value ) {
            $atts[ $key ] = $value;
        }

		$filepath = get_template_directory() . '/inc/widgets/image/views/widget.php';

        $instance = $atts;
        $before_widget = str_replace( 'class="', 'class="widget_sh_image ', $before_widget );

        if ( file_exists( $filepath ) ) {
            include ( $filepath );
        }
    }

    function update( $new_instance, $old_instance ) {
        // Unyson metaboxes
        if( defined( 'FW' ) && gillion_framework() == 'unyson' ) :

            return fw_get_options_values_from_input(
                $this->options,
                FW_Request::POST(fw_html_attr_name_to_array_multi_key($this->get_field_name($this->prefix)), array())
            );

        // Shufflehound metaboxes
        else :
            return Shufflehound_Metaboxes::widget_update( $new_instance, $old_instance, $this->options );
        endif;
    }

    function form( $values ) {
        // Unyson metaboxes
        if( defined( 'FW' ) && gillion_framework() == 'unyson' ) :

            $prefix = $this->get_field_id($this->prefix);
            $id = 'fw-widget-options-'. $prefix;

            echo '<div class="fw-force-xs fw-theme-admin-widget-wrap" id="'. esc_attr($id) .'">';
            echo fw()->backend->render_options($this->options, $values, array(
                'id_prefix' => $prefix .'-',
                'name_prefix' => $this->get_field_name($this->prefix),
            ));
            echo '</div>';
            $this->print_widget_javascript($id);

        // Shufflehound metaboxes
        else :
            $name_prefix = substr( $this->get_field_name(''), 0, -2 );
            echo Shufflehound_Metaboxes::widget( $this->options, $values, $name_prefix );
        endif;

        return $values;
    }

    private function print_widget_javascript($id) {
        ?><script type="text/javascript">
            jQuery(function($) {
                var selector = '#<?php echo esc_js($id) ?>', timeoutId;

                $(selector).on('remove', function(){ // ReInit options on html replace (on widget Save)
                    clearTimeout(timeoutId);
                    timeoutId = setTimeout(function(){ // wait a few milliseconds for html replace to finish
                        fwEvents.trigger('fw:options:init', { $elements: $(selector) });
                    }, 100);
                });
            });
        </script><?php
    }

}
