<?php
get_header(); ?>

    <div id="primary" class="content-area">
        <div id="content" class="clearfix">
            <?php

            query_posts(array(
                'post_type' => 'wp_team_members',
                'showposts' => get_option( 'posts_per_page' ),
                'orderby' => 'post_title',
                'order' => 'ASC'
            ) );

            if ( have_posts() ):

                    echo '<h1>Team members</h1>';
                    echo '<div class="row team-members" id="team-members">';

                    while ( have_posts() ) : the_post();
                        $post = get_post();
                        $image_url = wp_get_attachment_image_url( get_post_thumbnail_id( $post->ID ), 'medium' );
                        echo '
                            <div class="col-sm-5 item">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="t-image mx-auto" style="background-image: url('.$image_url.')"></div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <h5 class="title text-center mt-3">'.esc_html__(get_the_title($post)).'</h5>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12 text-center font-italic position">
                                            '.esc_html__(get_post_meta($post->ID, "wp_position", true)).'
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12 icons text-center">
                                            <a href="'.esc_html__(get_post_meta($post->ID, "wp_facebook_url", true)).'">
                                                <i class="fab fa-facebook-square"></i>
                                            </a>
                                            <a href="'.esc_html__(get_post_meta($post->ID, "wp_twitter_url", true)).'">
                                                <i class="fab fa-twitter-square"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12 text-justify t-content d-none">
                                            '.wp_filter_kses($post->post_content).'
                                        </div>
                                        <button class="btn-sm btn-primary mx-auto t-buttons">Read more</button>
                                    </div>
                                </div>
                        ';
                    endwhile;

                    echo '</div>';

                else :
                    echo "<p class='no-posts'>" . __( "Sorry, there are no team members" ) . "</p>";
                endif;
            ?>

        </div>
    </div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>