

jQuery(document).ready(function(){

	jQuery('form.form select.chosen').chosen({
		include_group_label_in_selected : true,
		search_contains:true
	}) ;

	initForm(jQuery('form.form')) ;

	jQuery('select[name$="[type]"]').trigger('change') ;

	checkTarifs() ;

	jQuery('span.glyphicon[title]').tooltip() ;

}) ;

jQuery(document).on('submit','form.form',function(e){
	
	//var ok = checkForm(jQuery(this)) ;
	var ok = true ;
		
	if ( ok === true )
	{
		jQuery('.submit').replaceWith('<div class="alert alert-warning loading">Formulaire en cours d\'enregistrement, veuillez patienter...</div>') ;
	}
	else
	{
		e.preventDefault() ;
		console.log('Votre formulaire comporte des erreurs : merci de remplir tous les champs obligatoires') ;
		alert('Votre formulaire comporte des erreurs : merci de remplir tous les champs obligatoires') ;
		jQuery('a#fiche').trigger('click') ;
		e.stopImmediatePropagation();
		return false ;
	}

}) ;

jQuery(document).on('change','form.form input[type="date"]',function(){

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
		fin.datepicker( "option", "minDate", v ).attr('min',v) ;
	}
	else if ( t == 'fin' )
	{
		var debut = jQuery(this).closest('.form').find('input[name="date['+i+'][debut]"]') ;
		if ( debut.val() == '' ) debut.val(v) ;
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
	var coord = jQuery(this).closest('tr').find('input[name$="[coordonnee]"]') ;
	if ( jQuery(this).val() == 201 ) coord.attr('type','tel').attr('placeholder','00 00 00 00 00') ; // Tél
	else if ( jQuery(this).val() == 204 ) coord.attr('type','email').attr('placeholder','xxx@yyyy.zz') ; // Mél
	else if ( jQuery(this).val() == 205 ) coord.attr('type','url').attr('placeholder','http://www.xxx.zzz') ; // Url
	else coord.attr('type','text').attr('placeholder','') ; // Standard
}) ;

jQuery(document).on('change','form.form input[name="gratuit"]',function(){checkTarifs();}) ;

jQuery(document).on('click','div.date span.input-group-addon',function(){
	jQuery(this).closest('div').find('button').trigger('click') ;
}) ;

jQuery(document).on('click','div.time span.input-group-addon',function(){
	jQuery(this).closest('div').find('input').focus() ;
}) ;

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
