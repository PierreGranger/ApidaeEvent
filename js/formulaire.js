

jQuery(document).ready(function(){

	jQuery('form.form select.chosen').chosen({
		include_group_label_in_selected : true,
		search_contains:true,
		width:'100%'
	}) ;

	initForm(jQuery('form.form')) ;

	jQuery('select[name$="[type]"]').each(function(){
		selectChange(jQuery(this),true) ;
	}) ;

	checkTarifs() ;

	jQuery('span.glyphicon[title]').tooltip() ;

}) ;



jQuery(document).on('submit','form.form',function(e){

	var ok = true ;
	var firstError = null ;

	jQuery(this).find('input[required],select[required],textarea[required]').each(function(){
		if ( ! valideChamp(jQuery(this)) )
		{
			ok = false ;
			if ( firstError == null ) firstError = jQuery(this) ;
		}
	}) ;

	jQuery(this).find('.mc [name$="\[coordonnee\]"]').each(function(){
		if ( ! valideChamp(jQuery(this),jQuery(this).closest('tr').find('select').val()) )
		{
			ok = false ;
			if ( firstError == null ) firstError = jQuery(this) ;
		}
	}) ;
	jQuery(this).find('.float').each(function(){
		if ( ! valideChamp(jQuery(this)) )
		{
			ok = false ;
			if ( firstError == null ) firstError = jQuery(this) ;
		}
	}) ;

	if ( ok === true )
	{
		jQuery(this).css('opacity',0.5) ;
		jQuery('input[type="submit"]').closest('div').replaceWith('<div class="alert alert-warning loading">Formulaire en cours d\'enregistrement, veuillez patienter...</div>') ;
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

jQuery(document).on('change','form.form input.date',function(){

	var reg = /date\[([0-9]+)\]\[(debut|fin)\]/i ;
	var match = jQuery(this).attr('name').match(reg) ;

	if ( match.length != 3 ) return false ;

	var i = match[1] ;
	var t = match[2] ; // debut|fin
	var v = jQuery(this).val() ;

	if ( t == 'debut' )
	{
		var fin = jQuery(this).closest('.form').find('input[name="date['+i+'][fin]"]') ;
		if ( fin.val() == '' ) fin.val(v) ;
		valideChamp(fin) ;
		fin.datepicker( "option", "minDate", v ).attr('min',v) ;
	}
	else if ( t == 'fin' )
	{
		var debut = jQuery(this).closest('.form').find('input[name="date['+i+'][debut]"]') ;
		if ( debut.val() == '' ) debut.val(v) ;
		valideChamp(debut) ;
		//debut.datepicker( "option", "maxDate", v ).attr('max',v) ;
	}

	jQuery(this).data('lastVal',v) ;

}) ;

jQuery(document).on('click','table td.plus .btn',function(){
	var ligne = jQuery(this).closest('tbody').find('tr').first().clone() ;
	var tr = jQuery(this).closest('tr') ;
	ligne.insertBefore(tr) ;
	ligne.find('td').first().addClass('moins').html(icon_moins) ;
	var champs = ligne.find('input, select') ;
	champs.each(function(i,v){
		jQuery(this).removeAttr('required') ;
		jQuery(this)/*.removeAttr('class').removeAttr('id')*/.removeAttr('placeholder').val('') ;
		jQuery(this).closest('div').removeClass('hasDatepicker') ;
	}) ;
	setIndent(jQuery(this).closest('table')) ;
	initForm(jQuery(this).closest('table')) ;
}) ;

jQuery(document).on('click','table td.moins',function(){
	jQuery(this).closest('tr').remove() ;
	setIndent(jQuery(this).closest('table')) ;
	initForm(jQuery(this).closest('table')) ;
}) ;

jQuery(document).on('change','select[name$="[type]"]',function(){
	selectChange(jQuery(this)) ;
}) ;

jQuery(document).on('change','form.form input[name="gratuit"]',function(){checkTarifs();}) ;

jQuery(document).on('click','div.date span.input-group-addon',function(){
	jQuery(this).closest('div').find('button').trigger('click') ;
}) ;

jQuery(document).on('click','div.time span.input-group-addon',function(){
	jQuery(this).closest('div').find('input').focus() ;
}) ;

jQuery(document).on('change','.mc [name$="\[coordonnee\]"]',function(){
	valideChamp(jQuery(this),jQuery(this).closest('tr').find('select').val()) ;
}) ;

jQuery(document).on('change','.float, select[required], textarea[required], input.date, input#nom',function(){
	valideChamp(jQuery(this)) ;
}) ;

function valideChamp(champ,type)
{
	console.log('validateChamp('+champ.attr('name')+')') ;
	champ.closest('.form-group').removeClass('has-error') ;
	var val = champ.val() ;
	if ( val == '' && ! champ.prop('required') ) return true ;

	if ( val == '' && champ.prop('required') )
	{
		champ.closest('.form-group').addClass('has-error') ;
		return false ;
	}

	if ( champ.hasClass('float') )
	{
		champ.val(val.replace(/[^0-9\.,]/g,'')) ;
		if ( ! champ.val().match(/^-?\d*([\.,]{1}\d+)?$/) )
		{
			champ.closest('.form-group').addClass('has-error') ;
			return false ;
		}
		return true ;
	}
	else if ( type == 201 ) // Téléphone
	{
		champ.val(val.replace(/[^0-9]/g,'')) ;
		var beautify = champ.val().match(/([0-9]{1,2})/g) ;
		if ( ! champ.val().match(/^[0-9]{10}$/) )
		{
			champ.closest('.form-group').addClass('has-error') ;
			return false ;
		}
		if ( typeof beautify == 'object' && beautify != null ) champ.val(beautify.join(' ')) ;
		return true ;
	}
	else if ( type == 204 ) // Mél
	{
		// https://stackoverflow.com/questions/46155/how-to-validate-email-address-in-javascript
		var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/ ;
		if ( ! re.test(val) )
		{
			champ.closest('.form-group').addClass('has-error') ;
			return false ;
		}
		return true ;
	}/*
	else if ( t == 205 ) // Site web
	{

	}*/
	return true ;
}



function selectChange(select,init=null)
{
	console.log('select[name$="[type]"].change') ;
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
			'buttonText' : ''

		} ;
		var optsTime = {
			'scrollDefault': '09:00',
			'timeFormat': 'H:i'
		} ;

		elem.find('input.date').not('.hasDatepicker').datepicker(optsDate).addClass('hasDatepicker').prop('min',today) ;
		elem.find('input.time').not('.hasDatepicker').timepicker(optsTime).addClass('hasDatepicker') ;
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
		elem.find('input.time').closest('div').not('.hasDatepicker').datetimepicker(optsTime).addClass('hasDatepicker') ;
	}

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
