

jQuery(document).ready(function(){

	$.datepicker.setDefaults( $.datepicker.regional[ "fr" ] );

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

}) ;

jQuery(document).on('click','form.form .btn-submit',function(){
	jQuery(this).closest('form.form').submit() ;
}) ;

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
		alert('Votre formulaire comporte des erreurs : merci de remplir tous les champs obligatoires') ;
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
		jQuery(this).removeClass('hasDatepicker hasTimepicker') ;
	}) ;
	setIndent(jQuery(this).closest('table')) ;
	initForm(jQuery(this).closest('table')) ;
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

jQuery(document).on('change','form.form input[name="gratuit"]',function(){
	checkTarifs();
}) ;

jQuery(document).on('change focusout','form.form select, form.form input, form.form textarea',function(){
		jQuery(this).closest('.form-group').toggleClass('has-error',!valideChamp(jQuery(this))) ;
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

		if ( t == 'debut' )
		{
			var fin = champ.closest('.form').find('input[name="date['+i+'][fin]"]') ;
			if ( fin.val() == '' ) fin.val(val) ;
			//valideChamp(fin) ;
			fin.datepicker( "option", "minDate", val ).attr('min',val) ;
		}
		else if ( t == 'fin' )
		{
			var debut = champ.closest('.form').find('input[name="date['+i+'][debut]"]') ;
			if ( debut.val() == '' ) debut.val(val) ;
			//valideChamp(debut) ;
		}

		champ.data('lastVal',val) ;
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
	}/*
	else if ( t == 205 ) // Site web
	{

	}*/
	return true ;
}



function selectChange(select,init=null)
{
	var coord = select.closest('tr').find('input[name$="[coordonnee]"]') ;
	if ( select.val() == 201 ) coord.attr('type','tel').attr('placeholder','00 00 00 00 00') ; // Tél
	else if ( select.val() == 204 ) coord.attr('type','email').attr('placeholder','xxx@yyyy.zz') ; // Mél
	else if ( select.val() == 205 ) coord.attr('type','url').attr('placeholder','http://www.xxx.zzz') ; // Url
	else coord.attr('type','text').attr('placeholder','') ; // Standard

	// On ne trigger par le changement de coordonnée lors du chargement du formulaire pour éviter d'avoir une erreur sur les champs obligatoires.
	if ( init !== true ) coord.trigger('change') ;
}







function checkTarifs() {
	jQuery('form.form input[name="gratuit"]').each(function(){
		jQuery(this).closest('form').find('div.champ.tarifs').toggle(( jQuery(this).is(':checked') !== true )) ;
		jQuery(this).closest('form').find('div.complement_tarif').toggle(( jQuery(this).is(':checked') !== true )) ;
	}) ;
}

function initForm(elem) {

	var typeDatePicker = 'jQuery' ; // jQuery|bootstrap
	
	if ( typeDatePicker == 'jQuery' )
	{
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

		elem.find('input.date').not('.hasDatepicker').datepicker(optsDate).addClass('hasDatepicker').prop('min',today) ;
		elem.find('input.time').not('.hasTimepicker').timepicker(optsTime).addClass('hasTimepicker') ;
	}
	else if ( typeDatePicker == 'bootstrap' )
	{
		var d = new Date() ;
		var month = d.getMonth()+1;
		var day = d.getDate();
		var today = d.getFullYear() + '-' + (month<10 ? '0' : '') + month + '-' + (day<10 ? '0' : '') + day;

		var optsDate = {
			'locale' : 'fr',
			'format' : 'DD/MM/YYYY',
			'minDate' : today,
			'useCurrent' : false,
		} ;
		var optsTime = {
			'locale' : 'fr',
			'format' : 'HH:mm',
			'useCurrent' : false
		} ;

		elem.find('input.date').closest('div').not('.hasDatepicker').datetimepicker(optsDate).addClass('hasDatepicker').find('input').prop('min',today) ;
		elem.find('input.time').closest('div').not('.hasTimepicker').datetimepicker(optsTime).addClass('hasTimepicker') ;
	}


	jQuery.datepicker.setDefaults( jQuery.datepicker.regional[ "fr" ] );

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