<?php
/**
 * Make all tables with the specified identifier sortable using the jQuery Tablesorter plugin.
 * http://webjawns.com/2009/11/jquery-tablesorter-helper-function-for-wordpress/
 * @uses wp_enqueue_script() To enqueue the jQuery Tablesorter script.
 *
 * @param string $identifier jQuery selector (.sortable, #element_id, table, etc.)
 * @param array $args Default is empty string. Array of arguments to pass to $.tablesorter()
 * @return void
 */
function make_table_sortable($identifier, $args = '') {
    global $sortable_tables;
 
    if ( in_array($identifier, (array)$sortable_tables) )
        return;
 
    wp_enqueue_script('jquery-tablesorter');
 
    $sortable_tables[] = array(
        'identifier' => $identifier,
        'args'       => $args
    );
 
    add_action('admin_print_footer_scripts', '_make_table_sortable');
}
 
function _make_table_sortable() {
    global $sortable_tables;
 
    if ( count( (array)$sortable_tables ) <= 0 )
        return;
?>
<script type="text/javascript">
<?php
    foreach ($sortable_tables as $sortable_table) {
        if ( !is_array($sortable_table['args']) ) {
            $arguments = '';
        } else {
            $arguments = '{';
 
            $args_count = sizeof($sortable_table['args']);
            $i = 0;
            foreach ($sortable_table['args'] as $k => $v) {
                $arguments .= $k . ': ' . $v;
                if (++$i != $args_count) $arguments .= ', ';
            }
 
            $arguments .= '}';
        }
?>
$('<?php echo esc_js($sortable_table['identifier']); ?>').tablesorter(<?php echo esc_js($arguments); ?>);
<?php } ?>
</script>
<?php
}