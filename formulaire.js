

	var optsDate = {
		'dateFormat' : 'dd/mm/yy',
		'minDate' : '+1d',
		'showOn' : 'button',
		'buttonText' : '',
		firstDay:1,
	} ;
	var optsTime = {
		'scrollDefault': '09:00',
		'timeFormat': 'H:i'
	} ;

	var today = new Date() ;

jQuery(document).ready(function(){

	jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "fr" ] );

	jQuery('form.form select.chosen').each(function(){
		var params = {
			include_group_label_in_selected : true,
			search_contains:true,
			width:'100%',
			no_results_text:'Aucun résultat trouvé'
		} ;
		if ( typeof jQuery(this).data('max_selected_options') == 'number' )
			params['max_selected_options'] = jQuery(this).data('max_selected_options') ;
		jQuery(this).chosen(params) ;
	}) ;

	initForm(jQuery('form.form')) ;

	jQuery('select[name$="[type]"]').each(function(){
		selectChange(jQuery(this),true) ;
	}) ;

	checkTarifs() ;

	jQuery('span.glyphicon[title]').tooltip() ;

	if ( jQuery('select[name="organisateur"]').length > 0 )
	{
		
	}

}) ;

jQuery(document).on('click','form.form .btn-submit',function(){
	jQuery(this).closest('form.form').submit() ;
}) ;

/**
 * à l'enregistrement on va parcourir tous les champs pour les vérifier.
 * 
 */
jQuery(document).on('submit','form.form',function(e){

	var ok = true ;
	var firstError = null ;

	jQuery(this).find('select, input, textarea').each(function(){
		var okChamp = valideChamp(jQuery(this),jQuery(this).closest('tr').find('select').val()) ;
		jQuery(this).closest('.form-group').toggleClass('has-error',!okChamp) ;
		if ( ! okChamp )
		{
			ok = false ;
			if ( firstError == null ) firstError = jQuery(this) ;
		}
	}) ;

	var erreurTarif = checkTypeTarifs() ;
	if ( erreurTarif !== true )
	{
		//alert(erreurTarif) ;
	}

	var erreurContacts = checkContacts() ;
	if ( erreurContacts !== true )
	{
		ok = false ;
	}

	if ( ok === true )
	{
		jQuery(this).css('opacity',0.5) ;
		jQuery('input.btn-submit').closest('div').replaceWith('<div class="alert alert-warning loading">Formulaire en cours d\'enregistrement, veuillez patienter...</div>') ;
		return true ;
	}
	else
	{
		if ( firstError !== null )
		{
			var disp = firstError.is(':hidden') ;
			if ( disp ) firstError.show() ;
			firstError.focus() ;
			if ( disp ) firstError.hide() ;
		}
		e.preventDefault() ;
		e.stopImmediatePropagation();
		alert('Votre formulaire comporte des erreurs : merci de vérifier les champs encadrés en rouge') ;
		return false ;
	}

}) ;


// Clone une ligne d'une table.
jQuery(document).on('click','table td.plus .btn',function(){
	var ligne = jQuery(this).closest('tbody').find('tr').first().clone() ;
	var tr = jQuery(this).closest('tr') ;
	ligne.insertBefore(tr) ;
	ligne.find('td').first().addClass('moins').html(icon_moins) ;
	var champs = ligne.find('input, select') ;
	champs.each(function(i,v){
		jQuery(this).removeAttr('required') ;
		jQuery(this).val('') ;
		if ( jQuery(this).closest('table').hasClass('mc') ) jQuery(this).attr('placeholder','') ;
		jQuery(this).removeClass('hasDatepicker hasTimepicker').attr('id',null) ;
	}) ;
	setIndent(jQuery(this).closest('table')) ;
	initForm(jQuery(this).closest('table')) ;
	valideTarifUnique() ;
}) ;

jQuery(document).on('click','table td.moins',function(){
	jQuery(this).closest('tr').remove() ;
	setIndent(jQuery(this).closest('table')) ;
	initForm(jQuery(this).closest('table')) ;
}) ;

jQuery(document).on('click','div.date span.input-group-addon',function(){
	jQuery(this).closest('div').find('button').trigger('click') ;
}) ;

jQuery(document).on('click','div.time span.input-group-addon',function(){
	jQuery(this).closest('div').find('input').focus() ;
}) ;










jQuery(document).on('change','select[name$="[type]"]',function(){
	selectChange(jQuery(this)) ;
}) ;

jQuery(document).on('change','input[type="url"]',function(){
	selectChange(jQuery(this).closest('tr').find('select[name$="[type]"]',true)) ;
}) ;

jQuery(document).on('change','form.form input[name="gratuit"]',function(){
	checkTarifs();
}) ;

jQuery(document).on('change focusout','form.form select, form.form input, form.form textarea',function(){
	jQuery(this).closest('.form-group').toggleClass('has-error',!valideChamp(jQuery(this))) ;
}) ;

jQuery(document).on('change','div.tarifs select[name^="tarifs"]',function(){
	valideTarifUnique() ;
}) ;

function valideChamp(champ)
{
	var type = null ;
	if ( typeof champ.attr('name') !== 'undefined' && champ.attr('name').match(/\[coordonnee\]$/) )
		type = champ.closest('tr').find('select').val() ;

	var val = champ.val() ;
	if ( val == '' && ! champ.prop('required') ) return true ;
	if ( val == '' && champ.prop('required') ) return false ;

	if ( champ.hasClass('date') )
	{
		var reg = /date\[([0-9]+)\]\[(debut|fin)\]/i ;
		var match = champ.attr('name').match(reg) ;

		if ( match.length != 3 ) return false ;

		var i = match[1] ;
		var t = match[2] ; // debut|fin

		if ( ! champ.val().match(/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/i) )
			return false ;

		if ( t == 'debut' )
		{
			var fin = champ.closest('.form').find('input[name="date\['+i+'\]\[fin\]"]') ;
			if ( fin.val() == '' ) fin.val(val) ;
			fin.datepicker("destroy") ;
			var newOptDate = jQuery.extend({},optsDate,{'minDate':val}) ;
			fin.datepicker(newOptDate) ;

			if ( champ.datepicker('getDate') > fin.datepicker('getDate') )
				fin.datepicker('setDate',val) ;
		}
		else if ( t == 'fin' )
		{
			var debut = champ.closest('.form').find('input[name="date\['+i+'\]\[debut\]"]') ;
			if ( debut.val() == '' ) debut.val(val) ;
		}

		//champ.data('lastVal',val) ;
	}
	else if ( champ.hasClass('time') )
	{
		champ.val(champ.val().replace(/[;,.-]/g,':')) ;
		champ.val(champ.val().replace(/[^0-9:]/g,'')) ;
		if ( ! champ.val().match(/^[0-9]{1,2}:[0-9]{2}$/) ) return false ;
	}
	else if ( champ.hasClass('float') )
	{
		champ.val(champ.val().replace(/[;\.,\-]/g,'.')) ;
		champ.val(champ.val().replace(/[^0-9\.]/g,'')) ;
		if ( ! champ.val().match(/^-?\d*([\.]{1}\d+)?$/) ) return false ;
	}
	else if ( type == 201 || champ.hasClass('telephone') ) // Téléphone
	{
		champ.val(val.replace(/[^0-9]/g,'')) ;
		var beautify = champ.val().match(/([0-9]{1,2})/g) ;
		if ( ! champ.val().match(/^[0-9]{10}$/) ) return false ;
		if ( typeof beautify == 'object' && beautify != null ) champ.val(beautify.join(' ')) ;
	}
	else if ( type == 204 || champ.hasClass('mail') ) // Mél
	{
		// https://stackoverflow.com/questions/46155/how-to-validate-email-address-in-javascript
		var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/ ;
		if ( ! re.test(val) ) return false ;
	}
	else if ( type == 205 ) // Site web
	{
		var re = /^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)$/ ;
		if ( ! re.test(val) ) return false ;
	}
	return true ;
}



function selectChange(select,init)
{
	var coord = select.closest('tr').find('input[name$="[coordonnee]"]') ;
	coord.closest('tr').find('small.h205').hide() ;

	if ( select.val() == 201 ) coord.attr('type','tel').attr('placeholder','00 00 00 00 00') ; // Tél
	else if ( select.val() == 204 ) coord.attr('type','email').attr('placeholder','xxx@yyyy.zz') ; // Mél
	else if ( select.val() == 205 )
	{
		coord.attr('type','url').attr('placeholder','http://www.xxx.zzz') ; // Url
		if ( coord.val() != '' ) coord.closest('tr').find('small.h205').show() ;
	}
	else coord.attr('type','text').attr('placeholder','') ; // Standard

	// On ne trigger par le changement de coordonnée lors du chargement du formulaire pour éviter d'avoir une erreur sur les champs obligatoires.
	if ( ! init ) valideChamp(coord) ;
}



/**
 * 
 */

function checkTypeTarifs() {
	var trs = jQuery('form.form div.tarifs table tbody tr') ;
	var erreurs = [] ;
	trs.each(function(){
		var inputs = jQuery(this).find('input') ;
		var select = jQuery(this).find('select') ;
		select.closest('.form-group').removeClass('has-error') ;
		var inputsRenseignes = false ;
		inputs.each(function(){
			if ( jQuery(this).val() != '' ) inputsRenseignes = true ;
		}) ;
		if ( inputsRenseignes && select.val() == '' )
		{
			erreurs.push('Vous devez renseigner le type de tarif') ;
			select.closest('.form-group').addClass('has-error') ;
		}
	}) ;
	if ( erreurs.length == 0 ) return true ;
	return erreurs ;
}
jQuery(document).on('change','form.form div.tarifs table tbody tr',checkTypeTarifs) ;



function checkTarifs() {
	jQuery('form.form input[name="gratuit"]').each(function(){
		jQuery(this).closest('form').find('div.champ.tarifs').toggle(( jQuery(this).is(':checked') !== true )) ;
		jQuery(this).closest('form').find('div.complement_tarif').toggle(( jQuery(this).is(':checked') !== true )) ;
		jQuery(this).closest('form').find('div.modes_paiement').toggle(( jQuery(this).is(':checked') !== true )) ;
	}) ;
}

function initForm(elem) {

	elem.find('input.date').not('.hasDatepicker').datepicker(optsDate).addClass('hasDatepicker').prop('min',today) ;
	elem.find('input.time').not('.hasTimepicker').timepicker(optsTime).addClass('hasTimepicker') ;
	jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "fr" ] );

	checkTarifs() ;

}

function setIndent(table) {
	var i = 0 ;
	table.find('tbody tr').each(function(){
		jQuery(this).find('input, select').each(function(){
			var trouve = jQuery(this).attr('name').match(/^(.*)\[([0-9]+)\](.*)/i) ;
			if ( trouve.length > 1 )
			{
				jQuery(this).attr('name',trouve[1]+'['+i+']'+trouve[3]) ;
			}
		}) ;
		i++ ;
	}) ;
}

function recaptchaKo(){
	jQuery('form.form input.btn-submit').closest('div.form-group').hide() ;
	jQuery('form.form div#recaptcha p').show() ;
}

function recaptchaOk()
{
	jQuery('form.form input.btn-submit').closest('div.form-group').show() ;
	jQuery('form.form div#recaptcha p').hide() ;
}

function valideTarifUnique()
{
	var selects = jQuery('form.form div.tarifs table tbody tr select[name^="tarifs"]') ;
	
	var used = [] ;
	selects.each(function(){
		if ( jQuery(this).val() != '' )
		{
			if ( used.indexOf(jQuery(this).val()) >= 0 )
				jQuery(this).val('') ;
			else
				used.push(jQuery(this).val()) ;
		}
	}) ;
	selects.each(function(){
		var options = jQuery(this).find('option') ;
		var select = jQuery(this) ;
		options.each(function(){
			var optVal = jQuery(this).attr('value') ;
			if ( optVal == select.val() ) ;
			else if ( used.indexOf(optVal) >= 0 )
			{
				jQuery(this).attr('disabled','disabled') ;
			}
			else
				jQuery(this).removeAttr('disabled') ;
		}) ;
	}) ;
}

/**
 * Optionnellement à l'intégration on peut rendre un contact obligatoire.
 * Dans ce cas on va contrôler à l'enregistrement si le contact est renseigné (à minima mail ou tel)
 */
function checkContacts() {
	
	var contacts = jQuery('fieldset.contacts') ;
	if ( ! contacts.hasClass('required') ) return true ;
	
	var ret = false ;

	contacts.find('input.telephone').each(function(){
		if ( jQuery(this).val() != "" ) ret = true ;
	}) ;

	contacts.find('input.mail').each(function(){
		if ( jQuery(this).val() != "" ) ret = true ;
	}) ;

	// Aucun MC de contact trouvé...
	if ( ret == false )
	{
		contacts.find('input.telephone').first().closest('.form-group').addClass('has-error') ;
		contacts.find('input.mail').first().closest('.form-group').addClass('has-error') ;
	}

	return ret ;

}
jQuery(document).on('change','fieldset.contacts',checkContacts) ;