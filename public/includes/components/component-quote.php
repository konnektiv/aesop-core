<?php

/**
 * Creates a styled quote with large type
 *
 * @since    1.0.0
 */
if ( ! function_exists( 'aesop_quote_shortcode' ) ) {

    function aesop_quote_shortcode( $atts ) {

        $defaults = array(
            'width'      => '100%',
            'background' => '#222222',
            'img'        => '',
            'text'       => '',
            'height'     => 'auto',
            'align'      => 'center',
            'size'       => '1',
            'parallax'   => '',
            'direction'  => '',
            'quote'      => __( 'People are made of stories, not atoms.', 'aesop-core' ),
            'cite'       => '',
            'type'       => 'block',
            'revealfx'   => '',
            'className'=>''
        );

        $atts     = apply_filters( 'aesop_quote_defaults', shortcode_atts( $defaults, $atts, 'aesop_quote' ) );

        // let this be used multiple times
        static $instance = 0;
        $instance++;
        $unique = sprintf( '%s-%s', get_the_ID(), $instance );

        // set component to content width
        $contentwidth = 'content' == $atts['width'] ? 'aesop-content' : false;

        // set size
        $size_unit = apply_filters( 'aesop_quote_size_unit', 'em', $unique );
        $size      = $atts['size'] ? sprintf( '%s%s', $atts['size'], $size_unit ) : false;

        // bg img
        $bgimg = $atts['img'] ? sprintf( 'background-image:url(%s);background-size:cover;background-position:center center;', esc_url( $atts['img'] ) ) : false;

        // bg color only if not block style
        $bgcolor = 'block' == $atts['type'] ? sprintf( 'background-color:%s;', $atts['background'] ) : false;

        if ( 'block' == $atts['type'] ) {
            if ( $atts['text'] == '' ) {
                $atts['text'] = '#FFFFFF';
            }
            $fgcolor = sprintf( 'color:%s;', esc_attr( $atts['text'] ) );
        } else {
            // for non-block quote color is ignored unless it has been changed from the default value
            if ( $atts['text'] == '' || ! strcasecmp( esc_attr( $atts['text'] ), '#ffffff' ) ) {
                $fgcolor = null;
            } else {
                $fgcolor = sprintf( 'color:%s;', esc_attr( $atts['text'] ) );
            }
        }


        // set styles
        // hide the component initially if revealfx is set
        $visibility = aesop_revealfx_set( $atts ) ? 'visibility:hidden;' : false;
        $style      = $bgcolor || $fgcolor || $atts['height'] || $atts['width'] || $visibility ? sprintf( 'style="%s%s%sheight:%s;width:%s;%s"', esc_attr( $bgcolor ), $bgimg, $fgcolor, esc_attr( $atts['height'] ), esc_attr( $atts['width'] ), $visibility ) : false;

        $isparallax = 'on' == $atts['parallax'] ? 'quote-is-parallax' : false;
        $lrclass    = 'left' == $atts['direction'] || 'right' == $atts['direction'] ? 'quote-left-right' : false;

        // has img class
        $imgclass = $atts['img'] ? 'quote-has-image' : false;

        // type
        $type = $atts['type'] ? sprintf( ' aesop-quote-type-%s', trim( $atts['type'] ) ) : false;

        // align
        $align = $atts['align'] ? sprintf( 'aesop-component-align-%s', esc_attr( $atts['align'] ) ) : null;

        // style/consolidated classes
        $css_class_array = array(
            $align,
            $type,
            $contentwidth,
            $isparallax,
            $lrclass,
            $imgclass
        );

        $css_classes = '';
        if ( ! empty( $css_class_array ) ) {
            foreach ( $css_class_array as $class ) {
                $css_classes .= ' ' . $class;
            }
        }
        // lets make sure scroll direction makes sense
        if ( $isparallax ) {
            if ( $atts['align'] == "left" ) {
                $atts['direction'] = 'right';
            } elseif ( $atts['align'] == "right" ) {
                $atts['direction'] = 'left';
            }
        }

        // core/custom classes
        $core_classes = $atts['className'].' '.(function_exists( 'aesop_component_classes' ) ? aesop_component_classes( 'quote' ) : null);
        if ( 'block' == $atts['type'] ) {
            $core_classes = $core_classes.' alignfull';
        }

        // cite
        $cite = $atts['cite'] ? apply_filters( 'aesop_quote_component_cite', sprintf( '<cite class="aesop-quote-component-cite">%s</cite>', aesop_component_media_filter( $atts['cite'] ) ) ) : null;

        ob_start();

        do_action( 'aesop_quote_before', $atts, $unique ); // action
        ?>
    <div id="aesop-quote-component-<?php echo esc_attr( $unique ); ?>" <?php echo aesop_component_data_atts( 'quote', $unique, $atts ); ?>
         class="aesop-component aesop-quote-component <?php echo $core_classes . ' ' . $css_classes; ?>" <?php echo $style; ?>>

        <?php if ( 'block' == $atts['type'] ): ?>
            <!-- Aesop Core | Quote -->
            <script>
                jQuery(document).ready(function ($) {

                    var moving = $('#aesop-quote-component-<?php echo esc_attr( $unique );?> blockquote'),
                        component = $('#aesop-quote-component-<?php echo esc_attr( $unique );?>');

                    // if parallax is on and we're not on mobile
                    <?php if ( 'on' == $atts['parallax'] && ! wp_is_mobile() ) { ?>

                    function scrollParallax() {
                        var height = $(component).height(),
                            offset = $(component).offset().top,
                            scrollTop = $(window).scrollTop(),
                            windowHeight = $(window).height(),
                            position = Math.round(scrollTop * 0.1);

                        // only run parallax if in view
                        if (offset + height <= scrollTop || offset >= scrollTop + windowHeight) {
                            return;
                        }

                        moving.css({'transform': 'translate3d(0px,-' + position + 'px, 0px)'});

                        <?php if ( 'left' == $atts['direction'] ) { ?>
                        moving.css({'transform': 'translate3d(-' + position + 'px, 0px, 0px)'});
                        <?php } elseif ( 'right' == $atts['direction'] ) { ?>
                        moving.css({'transform': 'translate3d(' + position + 'px, 0px, 0px)'});
                        <?php } ?>
                    }

                    component.waypoint({
                        offset: '100%',
                        handler: function (direction) {
                            $(this).toggleClass('aesop-quote-faded');

                            // fire parallax
                            scrollParallax();
                            $(window).scroll(function () {
                                scrollParallax();
                            });
                        }
                    });

                    <?php } else { ?>

                    moving.waypoint({
                        offset: '90%',
                        handler: function (direction) {
                            $(this).toggleClass('aesop-quote-faded');

                        }
                    });

                    <?php }//end if ?>

                });
            </script>

        <?php endif;

        do_action( 'aesop_quote_inside_top', $atts, $unique ); // action

        // new
        $bool_custom = false;
        $arr_args    = array(
            'align'   => $align,
            'atts'    => $atts,
            'cite'    => $cite,
            'fgcolor' => $fgcolor,
            'instance' => $instance,
            'size'    => $size,
            'unique'  => $unique
        );
        $bool_custom = apply_filters( 'aesop_quote_custom_view', $bool_custom, $arr_args );

        if ( $bool_custom === false ) {
            ?>

            <blockquote class="<?php echo sanitize_html_class( $align ); ?>"
                        style="font-size:<?php echo esc_attr( $size ); ?>;<?php echo $fgcolor ?>">
                <span><?php echo aesop_component_media_filter( $atts['quote'] ); ?></span>
                <?php echo $cite; ?>
            </blockquote>

            <?php do_action( 'aesop_quote_inside_bottom', $atts, $unique ); // action ?>

            </div>
            <?php
        }
        do_action( 'aesop_quote_after', $atts, $unique ); // action

        return ob_get_clean();
    }
}//end if
