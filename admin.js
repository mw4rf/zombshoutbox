function admin_annonces_modifier(id)
{
	document.getElementById('res_admin_annonces').innerHTML = "Veuillez patienter..."
	var texte = document.getElementById('admin_annonces_text_'+id).value;

	new Ajax.Updater(
		"res_admin_annonces",
		"controllers/controller_admin.php",
		{
			method:'post',
			evalScripts:true,
			parameters: {	action:"annonces_modifier",
							id:id,
							annonce:texte
						}
		})
}

function admin_annonces_retirer(id)
{
	document.getElementById('res_admin_annonces').innerHTML = "Veuillez patienter..."
	new Ajax.Updater(
		"",
		"controllers/controller_admin.php",
		{
			method:'post',
			parameters: {	action:"annonces_retirer",
							id:id
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"tab12",
					"views/tab_admin.php",
					{evalScripts:true})
			}
		})
}

function admin_poll_titre_modifier(id)
{
	document.getElementById('res_admin_sondages').innerHTML = "Veuillez patienter..."
	
	var titre = document.getElementById('admin_poll_titre_'+id).value;
	var expiration = document.getElementById('admin_poll_expiration_'+id).value;
		
	new Ajax.Updater(
		"",
		"controllers/controller_admin.php",
		{
			method:'post',
			parameters: {	action:"poll_titre_modifier",
							id:id,
							titre:titre,
							expiration:expiration
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"tab12",
					"views/tab_admin.php",
					{evalScripts:true})
			}
		})
}

function admin_poll_titre_retirer(id)
{
	document.getElementById('res_admin_sondages').innerHTML = "Veuillez patienter..."
			
	new Ajax.Updater(
		"",
		"controllers/controller_admin.php",
		{
			method:'post',
			parameters: {	action:"poll_titre_retirer",
							id:id,
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"tab12",
					"views/tab_admin.php",
					{evalScripts:true})
			}
		})
}

function admin_poll_option_modifier(id)
{
	document.getElementById('res_admin_sondages').innerHTML = "Veuillez patienter..."
	
	var option = document.getElementById('admin_poll_option_'+id).value;
					
	new Ajax.Updater(
		"",
		"controllers/controller_admin.php",
		{
			method:'post',
			parameters: {	action:"poll_option_modifier",
							id:id,
							option:option
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"tab12",
					"views/tab_admin.php",
					{evalScripts:true})
			}
		})
}

function admin_poll_option_retirer(id)
{
	document.getElementById('res_admin_sondages').innerHTML = "Veuillez patienter..."
	
	new Ajax.Updater(
		"",
		"controllers/controller_admin.php",
		{
			method:'post',
			parameters: {	action:"poll_option_retirer",
							id:id
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"tab12",
					"views/tab_admin.php",
					{evalScripts:true})
			}
		})
}

function admin_maintenance(option)
{
	new Ajax.Updater(
		"admin_maintenance",
		"controllers/controller_admin.php",
		{
			method:'post',
			evalScripts:true,
			parameters: {	action:"maintenance",
							option:option
						}
		})
}

function admin_maintenance_delsystem()
{
	new Ajax.Updater(
		"admin_maintenance",
		"controllers/controller_admin.php",
		{
			method:'post',
			evalScripts:true,
			parameters: {	action:"delsystem",
						}
		})
}

function admin_censure_mots(option,id)
{
	document.getElementById('res_admin_censure').innerHTML = "Veuillez patienter..."
	
	var mot = document.getElementById('mot_'+id).value
	new Ajax.Updater(
		"",
		"controllers/controller_censure.php",
		{
			method:'post',
			parameters: {	action:"mots",
							option:option,
							id:id,
							mot:mot
						}
						,
			onSuccess: function(transport){
				new Ajax.Updater(
					"tab12",
					"views/tab_admin.php",
					{evalScripts:true})
			}
		})
}

function admin_censure_users(option,id)
{
	document.getElementById('res_admin_censure').innerHTML = "Veuillez patienter..."
	
	if(option == 'ajouter')
	{
		var user = document.getElementById('user_censure_user_'+id).value;
		var frequence = document.getElementById('user_censure_frequence_'+id).value;
		var actif = 1;
	}
	else if(option == 'modifier' || option == 'supprimer')
	{
		var user = id;
		var frequence = document.getElementById('user_censure_frequence_'+id).value;
		var actif = document.getElementById('user_censure_actif_'+id).checked;
		if(actif) actif = 1; else actif = 0;
	}
	
	new Ajax.Updater(
		"",
		"controllers/controller_censure.php",
		{
			method:'post',
			parameters: {	action:"users",
							option:option,
							user:user,
							frequence:frequence,
							actif:actif
						}
						,
			onSuccess: function(transport){
				new Ajax.Updater(
					"tab12",
					"views/tab_admin.php",
					{evalScripts:true})
			}
		})
}

function admin_scripts()
{
	var scripts = document.getElementById('admin_scripts_textarea').value
	
	new Ajax.Updater(
		"res_admin_scripts",
		"controllers/controller_admin.php",
		{
			method:'post',
			evalScripts:true,
			parameters: {	action:"scripts",
							scripts:scripts
						}
		})
}


function admin_stats()
{
	document.getElementById('admin_stats').innerHTML = "<div class=\"prefs\" style=\"text-align:center;font-size:1.1em;font-weight:bold;margin-top:1em;\">Veuillez patienter...</div>"
	
	new Ajax.Updater(
		"admin_stats",
		"views/admin_stats.php")
}

function admin_sql()
{
	document.getElementById('res_admin_sql').style.height = '300px'
	document.getElementById('res_admin_sql').innerHTML = "<div class=\"prefs\" style=\"text-align:center;font-size:1.1em;font-weight:bold;margin-top:1em;\">Veuillez patienter...</div>"
	
	var query = document.getElementById('admin_sql_query').value
	var table = document.getElementById('admin_sql_table').options[document.getElementById('admin_sql_table').selectedIndex].value
		
	new Ajax.Updater(
		"res_admin_sql",
		"controllers/controller_admin.php",
		{
			method:'post',
			evalScripts:true,
			parameters: {	action:"sql",
							table:table,
							query:query
						}
		})
}

function admin_rooms_rename(id)
{
	name = document.getElementById('room_name_'+id).value
	
	new Ajax.Updater(
		"res_admin_rooms",
		"controllers/controller_admin.php",
		{
			method:'post',
			evalScripts:true,
			parameters: {	action:"rooms_rename",
							room_id:id,
							name:name
						}
		})
}

function admin_rooms_close(id)
{	
	new Ajax.Updater(
		"res_admin_rooms",
		"controllers/controller_admin.php",
		{
			method:'post',
			evalScripts:true,
			parameters: {	action:"rooms_close",
							room_id:id
						}
		})
}

function admin_rooms_publicize(id)
{	
	new Ajax.Updater(
		"res_admin_rooms",
		"controllers/controller_admin.php",
		{
			method:'post',
			evalScripts:true,
			parameters: {	action:"rooms_publicize",
							room_id:id
						}
		})
}

function admin_rooms_privatize(id)
{
	pass = document.getElementById('room_pass_'+id).value
	
	new Ajax.Updater(
		"res_admin_rooms",
		"controllers/controller_admin.php",
		{
			method:'post',
			evalScripts:true,
			parameters: {	action:"rooms_privatize",
							room_id:id,
							pass:pass
						}
		})
}

function admin_rooms_changepassword(id)
{
	pass = document.getElementById('room_changepassword_'+id).value
	
	new Ajax.Updater(
		"res_admin_rooms",
		"controllers/controller_admin.php",
		{
			method:'post',
			evalScripts:true,
			parameters: {	action:"rooms_changepassword",
							room_id:id,
							newpassword:pass
						}
		})
}

function admin_showlog()
{	
	new Ajax.Updater(
		"res_admin_log",
		"controllers/controller_admin.php",
		{
			method:'post',
			evalScripts:true,
			parameters: {	action:"showlog"
						}
		})
}

function admin_unban(victim)
{
	new Ajax.Updater(
		"res_admin_ban",
		"controllers/controller_admin.php",
		{
			method:'post',
			evalScripts:true,
			parameters: {	action:"unban",
							victim:victim
						}
		})
}
