{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}
{crmRegion name="Ebs-billing-block"}
{if $paymentProcessor.payment_processor_type EQ 'EBS'}
   <fieldset class="billing_name_address-group">
	<legend>{ts}Billing Name and Address{/ts}</legend>
	  {if $profileAddressFields}
    	<input type="checkbox" id="billingcheckbox" value=0> <label for="billingcheckbox">{ts}Billing Address is same as above{/ts}</label>
    {/if}
    <div class="crm-section billing_name_address-section">
        <div class="crm-section {$form.billing_first_name.name}-section">
	    <div class="label">{$form.billing_first_name.label}</div>
            <div class="content">{$form.billing_first_name.html}</div>
            <div class="clear"></div>
        </div>
        <div class="crm-section {$form.billing_last_name.name}-section">
	    <div class="label">{$form.billing_last_name.label}</div>
            <div class="content">{$form.billing_last_name.html}</div>
            <div class="clear"></div>
        </div>
        {assign var=n value=billing_street_address-$bltID}
        <div class="crm-section {$form.$n.name}-section">
					<div class="label">{$form.$n.label}</div>
            <div class="content">{$form.$n.html}</div>
            <div class="clear"></div>
        </div>
        {assign var=n value=billing_city-$bltID}
        <div class="crm-section {$form.$n.name}-section">
					<div class="label">{$form.$n.label}</div>
            <div class="content">{$form.$n.html}</div>
            <div class="clear"></div>
        </div>
        {assign var=n value=billing_country_id-$bltID}
        <div class="crm-section {$form.$n.name}-section">
					<div class="label">{$form.$n.label}</div>
            <div class="content">{$form.$n.html|crmReplace:class:big}</div>
            <div class="clear"></div>
        </div>
        {assign var=n value=billing_state_province_id-$bltID}
        <div class="crm-section {$form.$n.name}-section">
					<div class="label">{$form.$n.label}</div>
            <div class="content">{$form.$n.html|crmReplace:class:big}</div>
            <div class="clear"></div>
        </div>
        {assign var=n value=billing_postal_code-$bltID}
        <div class="crm-section {$form.$n.name}-section">
					<div class="label">{$form.$n.label}</div>
            <div class="content">{$form.$n.html}</div>
            <div class="clear"></div>
        </div>
	{assign var=n value=billing_phone-$bltID}
        <div class="crm-section {$form.$n.name}-section">
	    <div class="label">{$form.$n.label}</div>
            <div class="content">{$form.$n.html}</div>
            <div class="clear"></div>
        </div>
    </div>
</fieldset>
{if $profileAddressFields}
<script type="text/javascript">
{literal}
cj( function( ) {
  cj('#billingcheckbox').click( function( ) {
    sameAddress( this.checked ); // need to only action when check not when toggled, can't assume desired behaviour
  });
});

function sameAddress( setValue ) {
  {/literal}
  var addressFields = {$profileAddressFields|@json_encode};
  {literal}
  var locationTypeInProfile = 'Primary';
  var orgID = field = fieldName = null;
  if ( setValue ) {
    cj('.billing_name_address-section input').each( function( i ){
      orgID = cj(this).attr('id');
      field = orgID.split('-');
      fieldName = field[0].replace('billing_', '');
      if ( field[1] ) { // ie. there is something after the '-' like billing_street_address-5
        // this means it is an address field
        if ( addressFields[fieldName] ) {
          fieldName =  fieldName + '-' + addressFields[fieldName];
        }
      }
      cj(this).val( cj('#' + fieldName ).val() );
    });
    
    var stateId;
    cj('.billing_name_address-section select').each( function( i ){
      orgID = cj(this).attr('id');
      field = orgID.split('-');
      fieldName = field[0].replace('billing_', '');
      fieldNameBase = fieldName.replace('_id', '');
      if ( field[1] ) { 
        // this means it is an address field
        if ( addressFields[fieldNameBase] ) {
          fieldName =  fieldNameBase + '-' + addressFields[fieldNameBase];
        }
      }

      // don't set value for state-province, since
      // if need reload state depending on country
      if ( fieldNameBase == 'state_province' ) {
        stateId = cj('#' + fieldName ).val();
      }
      else {
        cj(this).val( cj('#' + fieldName ).val() ).change( );   
      }
    });

    // now set the state province
    // after ajax call loads all the states
    if ( stateId ) {
      cj('select[id^="billing_state_province_id"]').ajaxStop(function() {
        cj( 'select[id^="billing_state_province_id"]').val( stateId );
      });
    }  
  }
}
{/literal}
</script>
{/if}
{/if}

{/crmRegion}

