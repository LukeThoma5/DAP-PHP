<?php
// Helper file to allow hotplugging the config for how to connect to the JSreport server
// Store the template id of the payslip report and the server url
$JS_REPORT_URL = "http://localhost/api/report/";
$TEMPLATE_ID = "rkU6QyrMQ";
?>

<script>

    //Helper functions to add the config settings into the document
    function GET_JS_REPORT_URL() {
        return "<?php echo $JS_REPORT_URL; ?>";
    }

    function GET_TEMPLATE_ID() {
        return "<?php echo $TEMPLATE_ID; ?>";
    }
</script>