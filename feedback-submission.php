<?php
/*
Plugin Name: Feedback Submission
Description: A custom plugin for feedback submission.shortcode used [houzeo_feedback_list] for listing the  feedback,[houzeo_feedback_form] for feedback form

Version: 1.0
Author: AMAL K SURESH
*/

// Activation Hook
register_activation_hook(__FILE__, 'feedback_submission_activate');
// Deactivation Hook
register_deactivation_hook(__FILE__, 'feedback_submission_deactivate');
//when plugin uninstall ,it will remove the table created
register_uninstall_hook(__FILE__, 'feedback_submission_uninstall');

function feedback_submission_activate() {
    // Create the custom table on activation
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        phone varchar(15) NOT NULL,
        comment text NOT NULL,
        submitted_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY (id)
    )";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
//adding css file
function feedback_submission_enqueue_styles() {
    wp_enqueue_style('feedback-submission-css', plugins_url('css/feedback-submission.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'feedback_submission_enqueue_styles');


//add the js script file where the ajax function is called while user submit the feedback form ...
function feedback_submission_enqueue_scripts() {
    //add our js file to plugin for ajax call
    wp_enqueue_script('feedback-submission', plugins_url('js/feedback-submission.js', __FILE__), array('jquery'), '1.0', true);
    // Pass the Ajax URL to the script
    wp_localize_script('feedback-submission', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'feedback_submission_enqueue_scripts');

function feedback_submission_form() {
    ob_start();
    ?>
    <div id="feedback-form">
      <h2>We love to hear from you </h2>
      <p>Add your opinion about us </p>
        <form id="houzeo-feedback-form">
            <input type="text" name="name" placeholder="Name" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="tel" name="phone" placeholder="Phone (US format: XXX-XXX-XXXX)" required><br>
            <textarea name="comment" placeholder="Comment" required></textarea><br>
            <input type="submit" value="Submit">
        </form>
        <div id="feedback-message" class=""></div>

    </div>
    <?php
    echo do_shortcode('[houzeo_feedback_list]');
    return ob_get_clean();
}
add_shortcode('houzeo_feedback_form', 'feedback_submission_form');

function submit_feedback() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback';

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $comment = sanitize_text_field($_POST['comment']);

    $wpdb->insert(
        $table_name,
        array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'comment' => $comment,
            'submitted_at' => current_time('mysql', 1)
        )
    );
    if ($result !== false) {
        // The insertion was successful
        echo '<div class="sucess_cls">Feedback submitted successfully!</div>';
    } else {
        // The insertion failed
          echo '<div class="err_cls">Failed to submit feedback. Please try again.!</div>';

    }
    wp_die();
}
add_action('wp_ajax_submit_feedback', 'submit_feedback');
add_action('wp_ajax_nopriv_submit_feedback', 'submit_feedback');

//shortcode for listing the table data
function feedback_submission_feedback_list() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback';

    $feedback_data = $wpdb->get_results("SELECT * FROM $table_name");


    ob_start();
    ?>
    <div id="feedback-list" class="<?php if(!$feedback_data){ echo "hide_table";}?>">
        <h3>Here is the feedback so far received from our customers.</h3>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody>
                <?php
                 foreach ($feedback_data as $data)
                {
                 ?>
                    <tr>
                        <td><?php echo esc_html($data->name); ?></td>
                        <td><?php echo esc_html($data->email); ?></td>
                        <td><?php echo esc_html($data->phone); ?></td>
                        <td><?php echo esc_html($data->comment); ?></td>
                    </tr>
                <?php
              }
               ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();

}
add_shortcode('houzeo_feedback_list', 'feedback_submission_feedback_list');


//it will add the last feedback submitted by user to the feedback listing page on the success of ajax call
function refresh_feedback_list() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback';

    $last_feedback = $wpdb->get_row("SELECT * FROM $table_name ORDER BY id DESC LIMIT 1");

    if ($last_feedback) {
        echo '<tr>';
        echo '<td>' . esc_html($last_feedback->name) . '</td>';
        echo '<td>' . esc_html($last_feedback->email) . '</td>';
        echo '<td>' . esc_html($last_feedback->phone) . '</td>';
        echo '<td>' . esc_html($last_feedback->comment) . '</td>';
        echo '</tr>';
    }

    wp_die();
}
add_action('wp_ajax_refresh_feedback_list', 'refresh_feedback_list');
add_action('wp_ajax_nopriv_refresh_feedback_list', 'refresh_feedback_list');

function feedback_submission_deactivate()
{
  //add this that need to be done while deactivation
}
function feedback_submission_uninstall() {
    // Remove the custom table on deactivation
    global $wpdb;
    $table_name = $wpdb->prefix . 'feedback';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
}

?>
