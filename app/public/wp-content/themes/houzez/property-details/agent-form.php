<?php
global $post, $current_user, $ele_settings;
$return_array = houzez20_property_contact_form();
if(empty($return_array)) {
	return;
}

$agent_info = isset($ele_settings['agent_detail']) ? $ele_settings['agent_detail'] : 'yes';

$terms_page_id = houzez_option('terms_condition');
$terms_page_id = apply_filters( 'wpml_object_id', $terms_page_id, 'page', true );
$hide_form_fields = houzez_option('hide_prop_contact_form_fields');
$gdpr_checkbox = houzez_option('gdpr_hide_checkbox', 1);
$agent_display = houzez_get_listing_data('agent_display_option');
$property_id = houzez_get_listing_data('property_id');

$agent_number = $return_array['agent_mobile'];
$agent_whatsapp_call = $return_array['agent_whatsapp_call'];
$agent_mobile_call = $return_array['agent_mobile_call'];
if( empty($agent_number) ) {
	$agent_number = $return_array['agent_phone'];
	$agent_mobile_call = $return_array['agent_phone_call'];
}

$user_name = $user_email = '';
if(!houzez_is_admin()) {
	$user_name =  $current_user->display_name;
	$user_email =  $current_user->user_email;
}

$send_btn_class = 'btn-half-width';
if($return_array['is_single_agent'] == false || empty($agent_number) || wp_is_mobile() ) {
	$send_btn_class = 'btn-full-width';
}

$action_class = "houzez-send-message";
$login_class = '';
$dataModel = '';
if( !is_user_logged_in() ) {
	$action_class = '';
	$login_class = 'msg-login-required';
	$dataModel = 'data-toggle="modal" data-target="#login-register-form"';
}

$agent_email = is_email( $return_array['agent_email'] );

$agent_mobile_num = houzez_option('agent_mobile_num', 1 ); 
$agent_whatsapp_num = houzez_option('agent_whatsapp_num', 1);

$whatsappBtnClass = "hz-btn-whatsapp btn-full-width mt-10";
$messageBtnClass = "btn-full-width mt-10";

if( $agent_mobile_num != 1 && !wp_is_mobile() ) {
	$whatsappBtnClass = "hz-btn-whatsapp btn-half-width";
}
if( $agent_mobile_num != 1 && $agent_whatsapp_num != 1 && !wp_is_mobile() ) {
	$messageBtnClass = "btn-half-width";
}

if ($agent_email && $agent_display != 'none') {
?>
<div class="property-form-wrap">

	<?php 
	if(houzez_form_type()) {

		if( $agent_info == 'yes' ) {
			echo $return_array['agent_data'];
		}
		
		if(!empty(houzez_option('contact_form_agent_above_image'))) {
			echo do_shortcode(houzez_option('contact_form_agent_above_image'));
		}

	} else { ?>
		<div class="property-form clearfix">
			<form method="post" action="#">
				
				<?php 
				if( $agent_info == 'yes' ) {
					echo $return_array['agent_data']; 
				}?>

				<?php if( $hide_form_fields['name'] != 1 ) { ?>
				<div class="form-group">
				<input class="form-control sidebar-name-field" name="name" value="<?php echo esc_attr($user_name); ?>" type="text" placeholder="<?php echo houzez_option('spl_con_name', 'Name'); ?>" pattern="[A-Za-z\s]+" title="Name must contain only letters and spaces" required>
			</div><!-- form-group -->
			<?php } ?>

			<?php if( $hide_form_fields['phone'] != 1 ) { ?>	
			<div class="form-group">
				<input class="form-control sidebar-phone-field" name="mobile" value="" type="tel" placeholder="<?php echo houzez_option('spl_con_phone', 'Phone'); ?>" pattern="[0-9]{10}" title="Phone must be numbers only, 10 digits required" maxlength="10" inputmode="numeric" data-validate="phone" required>
			</div><!-- form-group -->
			<?php } ?>

			<div class="form-group">
				<input class="form-control" name="email" value="<?php echo esc_attr($user_email); ?>" type="email" placeholder="<?php echo houzez_option('spl_con_email', 'Email'); ?>" required>
			</div><!-- form-group -->

			<?php if( $hide_form_fields['message'] != 1 ) { ?>	
			<div class="form-group form-group-textarea">
				<textarea class="form-control hz-form-message" name="message" rows="4" placeholder="<?php echo houzez_option('spl_con_message', 'Message'); ?>"><?php echo houzez_option('spl_con_interested', "Hello, I am interested in"); ?> [<?php echo get_the_title(); ?>]</textarea>
			</div><!-- form-group -->	
			<?php } ?>

				<?php if( $hide_form_fields['usertype'] != 1 ) { ?>	
				<div class="form-group">
					<select name="user_type" class="selectpicker form-control bs-select-hidden" title="<?php echo houzez_option('spl_con_select', 'Select'); ?>">

						<?php if( houzez_option('spl_con_buyer') != "" ) { ?>
						<option value="buyer"><?php echo houzez_option('spl_con_buyer', "I'm a buyer"); ?></option>
						<?php } ?>

						<?php if( houzez_option('spl_con_tennant') != "" ) { ?>
						<option value="tennant"><?php echo houzez_option('spl_con_tennant', "I'm a tennant"); ?></option>
						<?php } ?>

						<?php if( houzez_option('spl_con_agent') != "" ) { ?>
						<option value="agent"><?php echo houzez_option('spl_con_agent', "I'm an agent"); ?></option>
						<?php } ?>

						<?php if( houzez_option('spl_con_other') != "" ) { ?>
						<option value="other"><?php echo houzez_option('spl_con_other', 'Other'); ?></option>
						<?php } ?>
					</select><!-- selectpicker -->
				</div><!-- form-group -->
				<?php } ?>

				<?php do_action('houzez_property_agent_contact_fields'); ?>

				<?php if( houzez_option('gdpr_and_terms_checkbox', 1) ) { ?>
				<div class="form-group">
					<label class="control control--checkbox m-0 hz-terms-of-use <?php if( $gdpr_checkbox ){ echo 'hz-no-gdpr-checkbox';}?>">
						<?php if( ! $gdpr_checkbox ) { ?>
						<input type="checkbox" name="privacy_policy">
						<span class="control__indicator"></span>
						<?php } ?>
						<div class="gdpr-text-wrap">
							<?php echo houzez_option('spl_sub_agree', 'By submitting this form I agree to'); ?> <a target="_blank" href="<?php echo esc_url(get_permalink($terms_page_id)); ?>"><?php echo houzez_option('spl_term', 'Terms of Use'); ?></a>
						</div>
						
					</label>
				</div><!-- form-group -->	
				<?php } ?>		
			
		        <input type="hidden" name="property_agent_contact_security" value="<?php echo wp_create_nonce('property_agent_contact_nonce'); ?>"/>
		        <input type="hidden" name="property_permalink" value="<?php echo esc_url(get_permalink($post->ID)); ?>"/>
		        <input type="hidden" name="property_title" value="<?php echo esc_attr(get_the_title($post->ID)); ?>"/>
		        <input type="hidden" name="property_id" value="<?php echo esc_attr($property_id); ?>"/>
		        <input type="hidden" name="action" value="houzez_property_agent_contact">
		        <input type="hidden" name="listing_id" value="<?php echo intval($post->ID)?>">
		        <input type="hidden" name="is_listing_form" value="yes">
		        <input type="hidden" name="agent_id" value="<?php echo intval($return_array['agent_id'])?>">
		        <input type="hidden" name="agent_type" value="<?php echo esc_attr($return_array['agent_type'])?>">

		        <?php get_template_part('template-parts/google', 'reCaptcha'); ?>
		        <div class="form_messages"></div>
				<button type="button" class="houzez-ele-button houzez_agent_property_form btn btn-secondary <?php echo esc_attr($send_btn_class); ?>">
					<?php get_template_part('template-parts/loader'); ?>
					<?php echo houzez_option('spl_btn_send', 'Send Email'); ?>
					
				</button>
				
				<?php if ( $return_array['is_single_agent'] == true && !empty($agent_number) && $agent_mobile_num && !wp_is_mobile() ) : ?>
				<a href="tel:<?php echo esc_attr($agent_mobile_call); ?>" data-property-id="<?php echo intval($post->ID); ?>" data-agent-id="<?php echo intval($return_array['agent_id'])?>" class="btn hz-btn-call btn-secondary-outlined btn-half-width">
					<!-- <button type="button" class="btn"> -->
						<span class="hide-on-click"><?php echo houzez_option('spl_btn_call', 'Call'); ?></span>
						<span class="show-on-click"><?php echo esc_attr($agent_number); ?></span>
					<!-- </button> -->
				</a>
				<?php endif; ?>

				<?php if( $return_array['is_single_agent'] == true && !empty($agent_whatsapp_call) && $agent_whatsapp_num ) { ?>
				<a target="_blank" href="https://api.whatsapp.com/send?phone=<?php echo esc_attr( $agent_whatsapp_call ); ?>&text=<?php echo houzez_option('spl_con_interested', "Hello, I am interested in").' ['.get_the_title().'] '.get_permalink(); ?>" data-property-id="<?php echo intval($post->ID); ?>" data-agent-id="<?php echo intval($return_array['agent_id'])?>" class="btn btn-secondary-outlined <?php echo esc_attr($whatsappBtnClass); ?>"><i class="houzez-icon icon-messaging-whatsapp mr-1"></i> <?php esc_html_e('WhatsApp', 'houzez'); ?></a>
				<?php } ?>

				<?php if( $return_array['is_single_agent'] == true && houzez_option('agent_direct_messages', 0) ) { ?>
				<button type="button" <?php echo $dataModel; ?> class="<?php echo esc_attr($action_class).' '.esc_attr($login_class); ?> btn btn-secondary-outlined <?php echo esc_attr($messageBtnClass); ?>">
					<?php get_template_part('template-parts/loader'); ?>
					<?php echo houzez_option('spl_btn_message', 'Send Message'); ?>		
				</button>
				<?php } ?>
			</form>
		</div><!-- property-form -->
		
	<?php } ?>
</div><!-- property-form-wrap -->

<script>
// Prevent invalid characters from being typed
document.addEventListener('keypress', function(e) {
    var isNameField = e.target.name === 'name' || e.target.classList.contains('sidebar-name-field');
    var isPhoneField = e.target.name === 'mobile' || e.target.classList.contains('sidebar-phone-field');
    
    // Name field - only allow letters and spaces
    if(isNameField) {
        var char = String.fromCharCode(e.which);
        if(!/[A-Za-z\s]/.test(char)) {
            e.preventDefault();
            showError(e.target, 'Name can only contain letters and spaces');
            return false;
        } else {
            clearError(e.target);
        }
    }
    
    // Phone field - only allow numbers
    if(isPhoneField) {
        var char = String.fromCharCode(e.which);
        if(!/[0-9]/.test(char)) {
            e.preventDefault();
            showError(e.target, 'Phone can only contain numbers');
            return false;
        } else {
            clearError(e.target);
        }
    }
});

// Handle keydown for control keys and 10 digit limit on phone
document.addEventListener('keydown', function(e) {
    var isPhoneField = e.target.name === 'mobile' || e.target.classList.contains('sidebar-phone-field');
    var isNameField = e.target.name === 'name' || e.target.classList.contains('sidebar-name-field');
    
    // Allow control keys: backspace, delete, arrow keys, tab
    var allowedKeys = [8, 9, 37, 38, 39, 40, 46];
    
    if(isPhoneField && e.target.value.length >= 10 && allowedKeys.indexOf(e.keyCode) === -1) {
        e.preventDefault();
        showError(e.target, 'Phone must be numbers only, 10 digits required');
        return false;
    }
    
    if(isNameField || isPhoneField) {
        clearError(e.target);
    }
});

// Validate phone field on blur and before form submission
document.addEventListener('blur', function(e) {
    var isPhoneField = e.target.name === 'mobile' || e.target.classList.contains('sidebar-phone-field');
    
    if(isPhoneField) {
        var phoneValue = e.target.value.trim();
        if(phoneValue.length > 0 && phoneValue.length !== 10) {
            showError(e.target, 'Phone must be numbers only, 10 digits required');
        } else if(phoneValue.length === 10) {
            clearError(e.target);
        }
    }
}, true);

// Validate on form submission
var agentForm = document.querySelector('.property-form form');
if(agentForm) {
    agentForm.addEventListener('submit', function(e) {
        var phoneField = agentForm.querySelector('input[name="mobile"]');
        if(phoneField) {
            var phoneValue = phoneField.value.trim();
            if(phoneValue.length !== 10 || !/^[0-9]{10}$/.test(phoneValue)) {
                e.preventDefault();
                showError(phoneField, 'Phone must be numbers only, 10 digits required');
                return false;
            }
        }
    });
}

// Paste handling
document.addEventListener('paste', function(e) {
    var isNameField = e.target.name === 'name' || e.target.classList.contains('sidebar-name-field');
    var isPhoneField = e.target.name === 'mobile' || e.target.classList.contains('sidebar-phone-field');
    
    if(isNameField || isPhoneField) {
        e.preventDefault();
        var pastedText = (e.clipboardData || window.clipboardData).getData('text');
        
        if(isNameField) {
            var filtered = pastedText.replace(/[^A-Za-z\s]/g, '');
            e.target.value = filtered;
            if(filtered.length > 0) {
                clearError(e.target);
            }
        } else if(isPhoneField) {
            var filtered = pastedText.replace(/[^0-9]/g, '').slice(0, 10);
            e.target.value = filtered;
            if(filtered.length > 0) {
                clearError(e.target);
            }
        }
    }
});

// Helper function to show error message
function showError(input, message) {
    var formGroup = input.closest('.form-group');
    if(!formGroup) return;
    
    // Remove existing error message
    var existingError = formGroup.querySelector('.error-message');
    if(existingError) {
        existingError.remove();
    }
    
    // Add error class and message
    input.classList.add('is-invalid');
    var errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '5px';
    errorDiv.textContent = message;
    formGroup.appendChild(errorDiv);
}

// Helper function to clear error message
function clearError(input) {
    var formGroup = input.closest('.form-group');
    if(!formGroup) return;
    
    input.classList.remove('is-invalid');
    var errorDiv = formGroup.querySelector('.error-message');
    if(errorDiv) {
        errorDiv.remove();
    }
}
</script>
<?php } ?>
