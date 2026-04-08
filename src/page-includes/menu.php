<?php
include_once __DIR__ . '/../function-includes/init.php';

/**
 * Load menu from laravel
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../function-includes/laravel_init.php';

echo $viewBlade->make('includes.menus')->render();

$booking = getenv('ENABLE_FACILITY_BOOKING_MENU');
?>
<script>
    window.facilityBookingEnabled = '<?php echo  $booking; ?>' == 'true' ? true : false;
</script>