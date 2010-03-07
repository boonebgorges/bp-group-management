<?php


function bp_group_management_admin_add() {
	$plugin_page = add_submenu_page( 'bp-general-settings', __('Group Management','bp-group-management'), __('Group Management','bp-group-management'), 'manage_options', __FILE__, 'bp_group_management_admin_screen' );
	add_action('admin_print_styles-' . $plugin_page, 'bp_group_management_css');
}
add_action( 'admin_menu', 'bp_group_management_admin_add', 70 );


function bp_group_management_css() {
	wp_enqueue_style( 'bp-group-management-css' );
}


function bp_group_management_admin_screen() {
	global $wpdb;

	do_action( 'bp_gm_action' );
	
	switch( $_GET['action'] ) {
		case "edit":
			bp_group_management_admin_edit();
			break;
		
		case "delete":
			bp_group_management_admin_delete();
			break;
	
		default:
			bp_group_management_admin_main();
	}
}


/* Creates the main group listing page (Dashboard > BuddyPress > Group Management */
function bp_group_management_admin_main() {

	/* Group delete requests are sent back to the main page. This handles group deletions */
	if( $_GET['group_action'] == 'delete' ) {
		if ( !check_admin_referer( 'bp-group-management-action_group_delete' ) )
				return false;
				
		if ( !bp_group_management_delete_group( $_GET['id'] ) ) { ?>
			<div id="message" class="updated fade"><p><?php _e('Sorry, there was an error.', 'bp-group-management'); ?></p></div>
		<?php } else { ?>
			<div id="message" class="updated fade"><p><?php _e('Group deleted.', 'bp-group-management'); ?></p></div>
		<?php
			do_action( 'groups_group_deleted', $bp->groups->current_group->id );
		}
	}

	/* Orders the groups when the user clicks a column header */
	if( $_GET['order'] )
		$order = $_GET['order'];	
	?>

          <div class="wrap">
            <h2><?php _e( 'Group Management', 'bp-group-management' ) ?></h2>
            <br />
            <table width="100%" cellpadding="3" cellspacing="3" class="widefat">
			<thead>
				<tr>
					<th scope="col" class="check-column"></th>
            		<th scope="col"><a href="admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;order=group_id"><?php _e( 'Group ID', 'bp-group-management' ) ?></a></th>
            		<th scope="col"><a href="admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;order=name"><?php _e( 'Group Name', 'bp-group-management' ) ?></a></th>
            		<th scope="col"><a href="admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;order=group_id"><?php _e( 'Date Created', 'bp-group-management' ) ?></a></th>
            		<th scope="col"><a href="admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;order=popular"><?php _e( 'Number of Members', 'bp-group-management' ) ?></a></th>
            		
            		<?php do_action( 'bp_gm_group_column_header' ); ?>
            	</tr>
            </thead>
            
			<tbody id="the-list">
            	<?php
            	$args = array( 'type' => 'alphabetical' );
            	
            	if ( $order == 'name' )
            		$args = array( 'type' => 'alphabetical' );
            	else if ( $order == 'group_id' )
            		$args = array( 'type' => 'newest' );
            	else if ( $order == 'popular' )
            		$args = array( 'type' => 'popular' );
            	
            	if( bp_has_groups( $args ) ) : while( bp_groups() ) : bp_the_group(); ?>
            		<?php global $groups_template; 
            			  if ( !$group )
            			  		$group =& $groups_template->group;
            		?>
            		
            		<tr>
            			<th scope="row" class="check-column">
							
						</th>
						<th scope="row">
							<?php bp_group_id(); ?>
						</th>
						
						<td scope="row">
							<?php bp_group_name(); ?>
									<br/>
									<?php
									$controlActions	= array();
									$controlActions[]	= '<a href="admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;action=edit&amp;id=' . bp_get_group_id() . '" class="edit">' . __('Edit') . '</a>';								
									
									
									$controlActions[]	= '<a class="delete" href="admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;action=delete&amp;id=' . bp_get_group_id() . '">' . __("Delete") . '</a>';
									
									$controlActions[]	= "<a href='" . bp_get_group_permalink() ."' rel='permalink'>" . __('Visit', 'bp-group-management') . '</a>';
									
									$controlActions = apply_filters( 'bp_gm_group_action_links', $controlActions );
									
									?>
									
									<?php if (count($controlActions)) : ?>
									<div class="row-actions">
										<?php echo implode(' | ', $controlActions); ?>
									</div>
									<?php endif; ?>

							
						</td>
						
						<td scope="row">
							<?php echo $group->date_created; ?>
						</td>
						
						<td scope="row">
							<?php bp_group_total_members(); ?>
						</td>
						
						<?php do_action( 'bp_gm_group_column_data' ); ?>
						
						
            		</tr>
            	<?php endwhile; ?>
            	<?php else: ?>
            
            	<?php endif; ?>
            </tbody>
         	</table>

        </div>

<?php
	
}

function bp_group_management_admin_edit() {
?>
	<div class="wrap">
<?php

	$id = (int)$_GET['id'];
	$group = new BP_Groups_Group( $id, true );
	
	switch( $_GET['member_action'] ) {
		case "kick":
			if ( !check_admin_referer( 'bp-group-management-action_kick' ) )
				return false;

			if ( !bp_group_management_ban_member( $_GET['member_id'], $id ) ) { ?>
				<div id="message" class="updated fade"><p><?php _e('Sorry, there was an error.', 'bp-group-management'); ?></p></div>';
			<?php } else { ?>
				<div id="message" class="updated fade"><p><?php _e('Member kicked and banned', 'bp-group-management') ?></p></div>
			<?php }

			do_action( 'groups_banned_member', $_GET['member_id'], $id );
			
			break;
		
		case "unkick":
			if ( !check_admin_referer( 'bp-group-management-action_unkick' ) )
				return false;

			if ( !bp_group_management_unban_member( $_GET['member_id'], $id ) ) { ?>
				<div id="message" class="updated fade"><p><?php _e('Sorry, there was an error.', 'bp-group-management'); ?></p></div>
			<?php } else { ?>
				<div id="message" class="updated fade"><p><?php _e('Member unbanned', 'bp-group-management'); ?></p></div>
			<?php }

			do_action( 'groups_banned_member', $_GET['member_id'], $id );
			
			break;
		
		case "demote":
			if ( !check_admin_referer( 'bp-group-management-action_demote' ) )
				return false;

			if ( !groups_demote_member( $_GET['member_id'], $id ) ) { ?>
				<div id="message" class="updated fade"><p><?php _e('Sorry, there was an error.', 'bp-group-management'); ?></p></div>
			<?php } else { ?>
				<div id="message" class="updated fade"><p><?php _e('Member demoted', 'bp-group-management'); ?></p></div>
			<?php }

			do_action( 'groups_demoted_member', $_GET['member_id'], $id );
			
			break;
		
		case "mod":
			if ( !check_admin_referer( 'bp-group-management-action_mod' ) )
				return false;
			
			if ( !bp_group_management_promote_member( $_GET['member_id'], $id, 'mod' ) ) { ?>
				<div id="message" class="updated fade"><p><?php _e('Sorry, there was an error.', 'bp-group-management'); ?></p></div>
			<?php } else { ?>
				<div id="message" class="updated fade"><p><?php _e('Member promoted to moderator', 'bp-group-management'); ?></p></div>
			<?php }

			do_action( 'groups_promoted_member', $_GET['member_id'], $id );
			
			break;
		
		case "admin":
			if ( !check_admin_referer( 'bp-group-management-action_admin' ) )
				return false;
				
			if ( !bp_group_management_promote_member( $_GET['member_id'], $id, 'admin' ) ) { ?>
				<div id="message" class="updated fade"><p><?php _e('Sorry, there was an error.', 'bp-group-management'); ?></p></div>
			<?php } else { ?>
				<div id="message" class="updated fade"><p><?php _e('Member promoted to admin', 'bp-group-management'); ?></p></div>
			<?php }
			
			break;	
		
		case "add":
			if ( !check_admin_referer( 'bp-group-management-action_add' ) )
				return false;
			
			if ( !bp_group_management_join_group( $id, $_GET['member_id'] ) ) { ?>
				<div id="message" class="updated fade"><p><?php _e('Sorry, there was an error.', 'bp-group-management'); ?></p></div>
			<?php } else { ?>
				<div id="message" class="updated fade"><p><?php _e('User added to group', 'bp-group-management'); ?></p></div>
			<?php }
			
			break;
		
		do_action( 'bp_gm_member_action', $group, $id, $_GET['member_action'] );
	}
?>

	
	    <h2><?php _e( 'Group Management', 'bp-group-management' ) ?> : <?php echo bp_get_group_name( $group ); ?></h2>
	    <h4><a href="admin.php?page=bp-group-management/bp-group-management-bp-functions.php"><< <?php _e( 'Back to group index', 'bp-group-management' ) ?></a></h4>
		<div class="bp-gm-group-members">
		
		<?php if ( bp_group_has_members( 'group_id=' . $id . '&exclude_admins_mods=0&exclude_banned=0' ) ) { ?>
	    <h3><?php _e( 'Manage current and banned group members', 'bp-group-management' ) ?></h3>
	    
			<?php if ( bp_group_member_needs_pagination() ) : ?>

				<div class="pagination no-ajax">

					<div id="member-count" class="pag-count">
						<?php bp_group_member_pagination_count() ?>
					</div>

					<div id="member-admin-pagination" class="pagination-links">
						<?php bp_group_member_admin_pagination() ?>
					</div>

				</div>

			<?php endif; ?>

			<ul id="members-list" class="item-list single-line">
				<?php while ( bp_group_members() ) : bp_group_the_member(); ?>

					<?php if ( bp_get_group_member_is_banned() ) : ?>

						<li class="banned-user">
							<?php bp_group_member_avatar_mini() ?>
							<?php
								$unkicklink = "admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;action=edit&amp;id=" . $id . "&amp;member_id=" . bp_get_group_member_id() . "&amp;member_action=unkick";
								$unkicklink = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($unkicklink, 'bp-group-management-action_unkick') : $unkicklink;
							?>
							<?php bp_group_member_link() ?> <?php _e( '(banned)', 'buddypress') ?> <span class="small"> - <a href="<?php echo $unkicklink; ?>" class="confirm" title="<?php _e( 'Remove Ban', 'buddypress' ) ?>"><?php _e( 'Remove Ban', 'buddypress' ); ?></a>

					<?php else : ?>

						<li>
							<?php bp_group_member_avatar_mini() ?> 
							
							<?php
								$kicklink = "admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;action=edit&amp;id=" . $id . "&amp;member_id=" . bp_get_group_member_id() . "&amp;member_action=kick";
								$kicklink = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($kicklink, 'bp-group-management-action_kick') : $kicklink;

								$modlink = "admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;action=edit&amp;id=" . $id . "&amp;member_id=" . bp_get_group_member_id() . "&amp;member_action=mod";
								$modlink = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($modlink, 'bp-group-management-action_mod') : $modlink;
								
								$demotelink = "admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;action=edit&amp;id=" . $id . "&amp;member_id=" . bp_get_group_member_id() . "&amp;member_action=demote";
								$demotelink = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($demotelink, 'bp-group-management-action_demote') : $demotelink;
								
								$adminlink = "admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;action=edit&amp;id=" . $id . "&amp;member_id=" . bp_get_group_member_id() . "&amp;member_action=admin";
								$adminlink = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($adminlink, 'bp-group-management-action_admin') : $adminlink;
								
							?>
							<strong><?php bp_group_member_link() ?></strong>
							<span class="small"> - 
								<a href="<?php echo $kicklink; ?>" class="confirm" title="<?php _e( 'Kick and ban this member', 'buddypress' ); ?>"><?php _e( 'Kick &amp; Ban', 'buddypress' ); ?></a> | 
								<?php if ( groups_is_user_admin( bp_get_group_member_id(), $id ) ) : ?>
									<a href="<?php echo $demotelink; ?>" class="confirm" title="<?php _e( 'Demote to Member', 'buddypress' ); ?>"><?php _e( 'Demote to Member', 'buddypress' ); ?></a>								
								<?php elseif ( groups_is_user_mod( bp_get_group_member_id(), $id ) ) : ?>
									<a href="<?php echo $demotelink; ?>" class="confirm" title="<?php _e( 'Demote to Member', 'buddypress' ); ?>"><?php _e( 'Demote to Member', 'buddypress' ); ?></a> | <a href="<?php echo $adminlink; ?>" class="confirm" title="<?php _e( 'Promote to Admin', 'buddypress' ); ?>"><?php _e( 'Promote to Admin', 'buddypress' ); ?></a></span>
								<?php else : ?>
									<a href="<?php echo $modlink; ?>" class="confirm" title="<?php _e( 'Promote to Moderator', 'buddypress' ); ?>"><?php _e( 'Promote to Moderator', 'buddypress' ); ?></a> | <a href="<?php echo $adminlink; ?>" class="confirm" title="<?php _e( 'Promote to Admin', 'buddypress' ); ?>"><?php _e( 'Promote to Admin', 'buddypress' ); ?></a></span>								
								<?php endif; ?>

					<?php endif; ?>

							<?php do_action( 'bp_group_manage_members_admin_item' ); ?>
						</li>

				<?php endwhile; ?>
			</ul>


	
		<?php } ?>

		</div>
		
		
		<div class="bp-gm-add-members">
		<h3><?php _e('Add members to group', 'bp-group-management') ?></h3>
		<ul>
		<?php
			$members_obj = BP_Core_User::get_users('alphabetical');
			$members = $members_obj['users'];
			
			foreach( $members as $m ) {
				if( groups_is_user_member( $m->id, $id ) )
					continue;
				
				if( groups_is_user_banned( $m->id, $id ) )
					continue;
			
				$addlink = "admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;action=edit&amp;id=" . $id . "&amp;member_id=" . $m->id . "&amp;member_action=add";
				$addlink = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($addlink, 'bp-group-management-action_add') : $addlink;
			?>
			<ul>
				<strong><a href="<?php echo $addlink; ?>"><?php _e( 'Add', 'bp-group-management' ) ?></a></strong> - <?php echo $m->display_name; ?>
			</ul>
			<?php }
			
			//print "<pre>"; print_r($members);
		?>
		</ul>
		</div>		
		
	</div>
<?php
}


function bp_group_management_admin_delete() {
	
	$id = (int)$_GET['id'];
	$group = new BP_Groups_Group( $id, true );
	$deletelink = "admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;group_action=delete&amp;id=" . $id;
	$deletelink = ( function_exists('wp_nonce_url') ) ? wp_nonce_url($deletelink, 'bp-group-management-action_group_delete') : $deletelink;
	$backlink = "admin.php?page=bp-group-management/bp-group-management-bp-functions.php&amp;action=edit&amp;id=" . $id;

?>
	
	<div class="wrap">
	 	<h2><?php _e( 'Group Management', 'bp-group-management' ) ?> : <?php echo bp_get_group_name( $group ); ?></h2>
	 	<h4><a href="admin.php?page=bp-group-management/bp-group-management-bp-functions.php"><< <?php _e( 'Back to group index' ) ?></a></h4>
	 	
	 	
	 	<h3><?php _e( 'Deleting the group', 'bp-group-management' ); ?> <?php echo '"' . bp_get_group_name( $group ) . '"'; ?></h3>
	 	<p><?php _e( 'You are about to delete the group', 'bp-group-management' ) ?> <em><?php echo bp_get_group_name( $group ); ?></em>. <strong><?php _e( 'This action cannot be undone.', 'bp-group-management' ) ?></strong></p>
	 	
	 	<p><a class="button-primary action" href="<?php echo $deletelink; ?>"><?php _e( 'Delete Group', 'bp-group-management' ) ?></a> 
	 	<a class="button-secondary action" href="<?php echo $backlink; ?>"><?php _e('Oops, I changed my mind') ?></a></p>
	</div>
<?php
}


/* The next few functions recreate core BP functionality, minus the check for $bp->is_item_admin and with some tweaks to the returned values */
function bp_group_management_ban_member( $user_id, $group_id ) {
	global $bp;
		
	$member = new BP_Groups_Member( $user_id, $group_id );

	do_action( 'groups_ban_member', $group_id, $user_id );

	if ( !$member->ban() )
		return false;

	update_usermeta( $user_id, 'total_group_count', (int)$total_count - 1 );
	
	return true;
}

function bp_group_management_unban_member( $user_id, $group_id ) {
	global $bp;

	$member = new BP_Groups_Member( $user_id, $group_id );

	do_action( 'groups_unban_member', $group_id, $user_id );

	return $member->unban();
}

function bp_group_management_promote_member( $user_id, $group_id, $status ) {
	global $bp;

	$member = new BP_Groups_Member( $user_id, $group_id );

	do_action( 'groups_promote_member', $group_id, $user_id, $status );

	return $member->promote( $status );
}

function bp_group_management_delete_group( $group_id ) {
	global $bp;
	
	$group = new BP_Groups_Group( $group_id );

	if ( !$group->delete() )
		return false;

	/* Delete all group activity from activity streams */
	if ( function_exists( 'bp_activity_delete_by_item_id' ) ) {
		bp_activity_delete_by_item_id( array( 'item_id' => $group_id, 'component' => $bp->groups->id ) );
	}

	// Remove all outstanding invites for this group
	groups_delete_all_group_invites( $group_id );

	// Remove all notifications for any user belonging to this group
	bp_core_delete_all_notifications_by_type( $group_id, $bp->groups->slug );

	do_action( 'groups_delete_group', $group_id );

	return true;
}

function bp_group_management_join_group( $group_id, $user_id = false ) {
	global $bp;

	if ( !$user_id )
		$user_id = $bp->loggedin_user->id;

	/* Check if the user has an outstanding invite, is so delete it. */
	if ( groups_check_user_has_invite( $user_id, $group_id ) )
		groups_delete_invite( $user_id, $group_id );

	/* Check if the user has an outstanding request, is so delete it. */
	if ( groups_check_for_membership_request( $user_id, $group_id ) )
		groups_delete_membership_request( $user_id, $group_id );

	/* User is already a member, just return true */
	if ( groups_is_user_member( $user_id, $group_id ) )
		return true;

	if ( !$bp->groups->current_group )
		$bp->groups->current_group = new BP_Groups_Group( $group_id );

	$new_member = new BP_Groups_Member;
	$new_member->group_id = $group_id;
	$new_member->user_id = $user_id;
	$new_member->inviter_id = 0;
	$new_member->is_admin = 0;
	$new_member->user_title = '';
	$new_member->date_modified = gmdate( "Y-m-d H:i:s" );
	$new_member->is_confirmed = 1;

	if ( !$new_member->save() )
		return false;

	/* Record this in activity streams */
	groups_record_activity( array(
		'user_id' => $user_id,
		'action' => apply_filters( 'groups_activity_joined_group', sprintf( __( '%s joined the group %s', 'buddypress'), bp_core_get_userlink( $user_id ), '<a href="' . bp_get_group_permalink( $bp->groups->current_group ) . '">' . attribute_escape( $bp->groups->current_group->name ) . '</a>' ) ),
		'type' => 'joined_group',
		'item_id' => $group_id
	) );

	/* Modify group meta */
	groups_update_groupmeta( $group_id, 'total_member_count', (int) groups_get_groupmeta( $group_id, 'total_member_count') + 1 );
	groups_update_groupmeta( $group_id, 'last_activity', gmdate( "Y-m-d H:i:s" ) );

	do_action( 'groups_join_group', $group_id, $user_id );

	return true;
}

?>