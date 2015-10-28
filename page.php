<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * To generate specific templates for your pages you can use:
 * /mytheme/views/page-mypage.twig
 * (which will still route through this PHP file)
 * OR
 * /mytheme/page-mypage.php
 * (in which case you'll want to duplicate this file and save to the above path)
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

if(isset($_POST['submitted'])) {

    $goals = array(
        'getahome' => 'Get a Home',
        'betterterms' => 'Get Better Loan or Credit Card Terms',
        'financialhealth' => 'Increase Your Financial Health',
        'relievestress' => 'Relieve Financial Stress',
        'improvecredit' => 'Improve Your Credit Report'
    );

    $body = "Form submission from damianfalcone.com\n\n";

    $body .= get_debt_types();
    $body .= 'Goal: ' . $goals[$_POST['goal']] . "\n\n";
    $body .= 'First Name: ' . $_POST['firstname'] . "\n\n";
    $body .= 'Last Name: ' . $_POST['lastname'] . "\n\n";
    $body .= 'Phone: ' . $_POST['phone'] . "\n\n";
    $body .= 'Email: ' . $_POST['email'] . "\n\n";
    $body .= 'Questions/Comments: ' . $_POST['comments'];

    require('../../../wp-config.php');
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . RECAPTCHA_SECRET . "&response=".$_POST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR']);
    $recaptchaResult = json_decode($response);
    if($recaptchaResult->success) {
        $emailTo = get_option('falcone_contact_emailform');
        if (!isset($emailTo) || ($emailTo == '') ){
            $emailTo = get_option('admin_email');
        }
        $subject = '[Form Submission] From damianfalcone.com';
        $headers = 'From: '.$name.' <'.$emailTo.'>' . "\r\n" . 'Reply-To: ' . $_POST['email'];

        wp_mail($emailTo, $subject, $body, $headers);
        $emailSent = true;

        header("Location: " . get_option('siteurl') . "/success");
        die();
    }
}

function get_debt_types() {
    $debtTypes = array(
        'mortgage' => $_POST['mortgage'],
        'student' => $_POST['student'],
        'creditcard' => $_POST['creditcard'],
        'casinomarker' => $_POST['casinomarker'],
        'medical' => $_POST['medical'],
        'carloan' => $_POST['carloan'],
        'irstaxdebt' => $_POST['irstaxdebt'],
        'timeshare' => $_POST['timeshare'],
        'rv' => $_POST['rv'],
        'lineofcredit' => $_POST['lineofcredit'],
        'other' => $_POST['other']
    );

    $debtNames = array(
        'mortgage' => 'Mortgage',
        'student' => 'Student',
        'creditcard' => 'Credit Card',
        'casinomarker' => 'Casino Marker',
        'medical' => 'Medical',
        'carloan' => 'Car Loan',
        'irstaxdebt' => 'IRS Tax Debt',
        'timeshare' => 'Time-Share',
        'rv' => 'Recreational Vehicle',
        'lineofcredit' => 'Line of Credit',
        'other' => 'Other'
    );

    $submittedDebts = array_filter($debtTypes);
    $debtResults = 'Debt Types: ';
    $i = 0;
    foreach($submittedDebts as $key => $submittedDebt) {
        $i++;
        $debtResults .= $debtNames[$key];
        $debtResults .= $i !== count($submittedDebts) ? ', ' : ".\n\n";
    }

    return $debtResults;
}

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

if(isset($post->home_highlighted_results)) {
    // Home page allows user to select multiple results to highlight
    // These will be post IDs of posts that are of the result custom post type
    $highlighted_results = $post->home_highlighted_results;
    $timber_results = array();
    foreach($highlighted_results as $result) {
        $timber_results[] = new TimberPost($result);
    }
    $context['timber_results'] = $timber_results;

    foreach($timber_results as $result) {
        // Removing galleries (and any other shortcodes)
        $result->post_content = strip_shortcodes($result->post_content);
    }
}
Timber::render( array( 'page-' . $post->post_name . '.twig', 'page.twig' ), $context );