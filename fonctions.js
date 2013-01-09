// Compte du nombre de divs de messages
var MSG_DIV = 0;

// valider avec entrée
document.onkeypress = enterk;

var tabs;

var SweetTitlesLoaded = false;

// Au chargement de la page
document.observe('dom:loaded',function(){
tabs = new Control.Tabs('tabs');
//commandesmenu();
});

// Ajax Updaters
var updater_twitter, updater_sondages, updater_calendrier, /*updater_notes,*/ updater_cronusers, updater_today, updater_mp, updater_xml, updater_shoutbox, updater_sidebar

//-----------
function waitwl()
{
	$('preload').style.visibility='hidden';
	$('preload_container').style.visibility='hidden';
	
	$('twitterbar').style.display = 'block';
}

function loadSweetTitles()
{
	sweetTitles.tipOut()
	EventCache.flush()
	sweetTitles.init()
}

//-----------
function cron()
{	
	// Core
	afficher()
	
	// Recherche
	new Ajax.Updater(
		"tab_search",
		"views/tab_search.php"
		)
	
	// twitter => méthode PHP/feedburner
	updater_twitter = new Ajax.PeriodicalUpdater(
		"twitter_content",
		"twitter.php",
		{
		method: 'get',
		parameters: {	ajaxcall:'feedburner_read'
					},
		frequency: 60,
		decay: 0
		}
		)
	
	// Citations
	updater_citations = new Ajax.PeriodicalUpdater(
		"citationsbar",
		"views/partials/citations.php",
		{
		method: 'get',
		frequency: 60,
		decay: 0
		}
		)
		
	// MP
	new Ajax.Updater(
		"new_mp",
		"controllers/controller_mp.php"
		)
		
	// Sondages
	updater_sondages = new Ajax.PeriodicalUpdater(
		"rightbar",
		"sondages.php",
		{
		method: 'get',
		frequency: 60,
		decay: 0
		}
		)
	
	// Autres		
	updater_cronusers = new Ajax.PeriodicalUpdater(
		"",
		"cron_users.php",
		{
		method: 'get',
		frequency: 60,
		decay: 0
		}
		)
	
	updater_calendrier = new Ajax.PeriodicalUpdater(
		"tab_calendrier",
		"views/tab_calendrier.php",
		{
		method: 'get',
		frequency: 3600,
		decay: 0,
		evalScripts:true
		}
		)


	// updater_notes = new Ajax.PeriodicalUpdater(
	// 		"tab_notes",
	// 		"views/tab_notes.php",
	// 		{
	// 		method: 'get',
	// 		frequency: 60,
	// 		decay: 0
	// 		}
	// 		)
	
	new Ajax.Updater(
			"tab_notes",
			"views/tab_notes.php")
			
	updater_today = new Ajax.PeriodicalUpdater(
		"tab_today",
		"views/tab_today.php",
		{
		method: 'get',
		frequency: 60*10,
		decay: 0
		}
		)
		
	updater_mp = new Ajax.PeriodicalUpdater(
		"tab_mp",
		"views/tab_mp.php",
		{
		method: 'get',
		frequency: 300,
		decay: 0
		}
		)
		
	updater_xml = new Ajax.PeriodicalUpdater(
		"tab_xml",
		"views/tab_xml.php",
		{
		method: 'get',
		frequency: 900+Math.random()*100, // entre 15 et 30 minutes
		decay: 0,
		evalScripts:true
		}
		)

}

function ajaxrefresh(page)
{	
	$(page+'_rb').value = "Veuillez patienter..."
	$(page+'_rb').disabled = true
	
	new Ajax.Updater(
		page,
		"views/"+page+".php",
		{
			evalScripts:true
		}
		)
}

function ajaxrefreshoption(page,get_val)
{	
	
	new Ajax.Updater(
		page,
		"views/"+page+".php",
		{
		method:'get',
		parameters: {	get_prop:get_val,	},
		}
		)
}

function ajaxrefreshspecial(conteneur,page)
{	
	$(page+'_rb').value = "Veuillez patienter..."
	$(page+'_rb').disabled = true
	
	new Ajax.Updater(
		conteneur,
		"views/"+page+".php",
		{
			evalScripts:true
		}
		)
}

function todayroom()
{
	
	var room = $('today_room').options[$('today_room').selectedIndex].value
	new Ajax.Updater(
		"tab_today",
		"views/tab_today.php",
		{
		method:'post',
		parameters: {	room_id:room,	},
		}
		)
}

function initrating()
{
	var parias = new Control.Rating('parias');
}

function onlight(c)
{
	$(c).style.backgroundColor = bg_none;
}

function offlight(c)
{
	$(c).style.backgroundColor = bg_block;
}

// coalitons
function cit_coas(opt)
{
	if(opt == "new")
	{
		var membre = $('cit_coa_new_membre').options[$('cit_coa_new_membre').selectedIndex].value
		new Ajax.Updater(
			"coas_result",
			"controllers/controller_coas.php",
			{
				method:'post',
				parameters: {	a:'new',
								membre:membre
							},
			})
	}
	else if(opt == "modif")
	{
		var membre = $('cit_coa_modif_membre').options[$('cit_coa_modif_membre').selectedIndex].value
		var coa = $('cit_coa_modif_coas').options[$('cit_coa_modif_coas').selectedIndex].value
		new Ajax.Updater(
			"coas_result",
			"controllers/controller_coas.php",
			{
				method:'post',
				parameters: {	a:'modif',
								membre:membre,
								coa:coa
							},
			})
		
	}
	else if(opt == "del")
	{
		var coa = $('cit_coa_del_coas').options[$('cit_coa_del_coas').selectedIndex].value
		new Ajax.Updater(
			"coas_result",
			"controllers/controller_coas.php",
			{
				method:'post',
				parameters: {	a:'del',
								coa:coa
							},
			})
	}
}

function addcit(id)
{
	var note = $('cit_'+id);
	var button = $('cit_b_'+id);
	
	new Ajax.Updater(
		"",
		"controllers/controller_rencontres.php",
		{
			method:'post',
			parameters: {	a:'new',
							id:id,
							note:note.value
						},
			onSuccess: function(transport){
				button.value = "Enregistré !"
				button.disabled = true
				note.disabled = true
			}
		})	
}

function pariasform()
{
	new Ajax.Updater(
		"parias_form_response",
		"controllers/controller_parias.php",
		{
			method:'post',
			parameters: {	a:'new',
							nom:$('nom').value,
							raison:$('raison').value,
							priorite:$('priorite').value
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"pariascontainer",
					"views/view_parias.php")
			}
		})
}

function noteparia(id,note)
{
	new Ajax.Updater(
		"",
		"controllers/controller_parias.php",
		{
			method:'post',
			parameters: {	a:'note',
							note:note,
							id:id,
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"pariascontainer",
					"views/view_parias.php")
			}
		})
}

function rmvparia(id)
{
	new Ajax.Updater(
		"",
		"controllers/controller_parias.php",
		{
			method:'post',
			parameters: {	a:'rmv',
							id:id,
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"pariascontainer",
					"views/view_parias.php")
			}
		})
}
//-----------

function joinRoom(room_id)
{
	new Ajax.Updater(
		"",
		"controllers/controller_rooms.php",
		{
			method:'post',
			parameters: {	action:'join',
							room_id:room_id,
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"sidebartop",
					"users.php")
			}
		})
}

//-----------
function addvote(sondage,option)
{
	new Ajax.Updater(
		"",
		"controllers/controller_sondages.php",
		{
			method:'post',
			parameters: {	action:'vote',
							sondage_id:sondage,
							option_id:option,
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"rightbar",
					"sondages.php")
			}
		})
}

function delvote(sondage)
{
	new Ajax.Updater(
		"",
		"controllers/controller_sondages.php",
		{
			method:'post',
			parameters: {	action:'delvote',
							sondage_id:sondage,
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"rightbar",
					"sondages.php")
			}
		})
}

//-----------
function mpread()
{
	new Ajax.Updater('new_mp','controllers/controller_mp.php', {
	  method: 'get',
	  parameters: {mark: 'read'}
	  });
	
	new Ajax.Updater(
		"tab_mp",
		"views/tab_mp.php"
		)
}

function mpdelete()
{
	new Ajax.Updater('new_mp','controllers/controller_mp.php', {
	  method: 'get',
	  parameters: {mark: 'delete'},
		onSuccess: function(transport){
			new Ajax.Updater(
				"tab_mp",
				"views/tab_mp.php"
				)
		}
	  });
}

function delmp(id,step)
{
	// Confirmer
	if(step == 1)
	{
		var confirmation = document.createElement('div')
		Element.extend(confirmation)
		var confid = Element.identify(confirmation)
		var confmessage = "Confirmer la suppression ? <a href=\"#\" onclick=\"delmp('"+id+"','2');\">Oui</a> | <a href=\"#\" onclick=\"delmp('"+confid+"','3');\">Non</a>"
		Element.update(confirmation,confmessage)
		confirmation.addClassName('confirmation').show()
		$(id).appendChild(confirmation)
	}
	// Supprimer
	else if(step == 2)
	{
		new Ajax.Updater('','controllers/controller_mp.php', {
		  method: 'get',
		  parameters: {mark:'delete', mpid:id},
			onSuccess: function(transport){
				new Ajax.Updater(
					"tab_mp",
					"views/tab_mp.php"
					)
			}
		  });
	}
	// Annuler
	else if(step == 3)
	{
		Element.remove(id)
	}
}

function mpsort()
{
	var system = $('mpsystem').options[$('mpsystem').selectedIndex].value;
	var limit = $('mplimit').options[$('mplimit').selectedIndex].value;
	
	new Ajax.Updater('innermplist','views/view_mp.php', {
	  method: 'post',
	  parameters: {mpsystem: system,
				   mplimit: limit}
	  });
}

//-----------
function noteform()
{	
	var action = 'new'
	new Ajax.Updater(
		"",
		"controllers/controller_notes.php",
		{
			method:'post',
			parameters: {	action:action,
							note:$('note').value
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"tab_notes",
					"views/tab_notes.php")
			}
		})
}

function delnote(id)
{
	var action = 'delete'
	new Ajax.Updater("tab_notes","controllers/controller_notes.php", {
	  method:'post',
	  parameters: {action:action, delid:id},
		onSuccess: function(transport){
			new Ajax.Updater(
				"tab_notes",
				"views/tab_notes.php"
				)
		}
	  });
}

function copynote(id)
{
	var message = $('message')
	var note = $('note_'+id).innerHTML
	message.value = note
	tabs.setActiveTab('tab1')
	message.focus()
}

function modnote(id,step)
{
	var area = $('modnote_area_' + id)
	var container = $('modnote_' + id)
	var display = $('note_' + id)
	
	if(step == 1)
	{
		if(display.style.display != 'none')
		{
			display.style.display = 'none'
			container.style.display = 'block'
		}
		else
		{
			display.style.display = 'block'
			container.style.display = 'none'
		}
	}
	else if(step == 2)
	{
		var action = 'update'
		var note = area.value
		
		new Ajax.Updater("","controllers/controller_notes.php", {
		  method:'post',
		  parameters: {action:action, note_id:id, note:note},
			onSuccess: function(transport){
				new Ajax.Updater(
					"tab_notes",
					"views/tab_notes.php"
					)
			}
		  });
	}
	else return false;
}

function togglesharenote(id)
{
	var container = $('sharenote_container_' +  id)
	
	if(container.style.display == 'block')
		container.style.display = 'none'
	else
	{
		new Ajax.Updater(container,"views/partials/share_note.php", {
		  method:'post',
		  parameters: {note_id:id},
			onSuccess: function(transport){
				container.style.display = 'block'
			}
		  });
	}	
}

function sharenotewith(id)
{
	var container = $('sharenote_container_' +  id)
	var reader = $('sharenotesselect_' + id).options[$('sharenotesselect_' + id).selectedIndex].value
	
	new Ajax.Updater(container,"controllers/controller_notes.php", {
	  method:'post',
	  parameters: {action:'sharenotewith', note_id:id, reader:reader},
			onSuccess: function(transport){
				new Ajax.Updater(container,"views/partials/share_note.php", {
				  method:'post',
				  parameters: {note_id:id}
				  });
			}
	  });
}

function unsharenotewith(id)
{
	var container = $('sharenote_container_' +  id)
	var reader = $('unsharenotesselect_' + id).options[$('unsharenotesselect_' + id).selectedIndex].value
	
	new Ajax.Updater(container,"controllers/controller_notes.php", {
	  method:'post',
	  parameters: {action:'unsharenotewith', note_id:id, reader:reader},
			onSuccess: function(transport){
				new Ajax.Updater(container,"views/partials/share_note.php", {
				  method:'post',
				  parameters: {note_id:id}
				  });
			}
	  });
}

function cancelsharenote(id,reader)
{
	new Ajax.Updater('',"controllers/controller_notes.php", {
	  method:'post',
	  parameters: {action:'unsharenotewith', note_id:id, reader:reader},
			onSuccess: function(transport){
				new Ajax.Updater("tab_notes", "views/tab_notes.php");
			}
	  });
}

//-----------
function afficher()
{
	updater_shoutbox = new Ajax.PeriodicalUpdater(
		"shoutbox", 
		"afficher.php", 
		{
		method: 'get',
		frequency: 1, // fréquence de chaque requête, en secondes
    	decay: 1, // coeff. de délai de la prochaine requête lorsque la présence requête n'apporte pas de contenu nouveau
		insertion: Insertion.Bottom,
		onSuccess: function(transport){
		   	var alreadydone = $('alreadydone')
			var lastchild = $('shoutbox').lastChild
			var lastid = $('shoutbox').lastChild.id
			var shoutbox = $('shoutbox')
			// Affichage
			if(alreadydone.value != lastid)
			{	
				new Effect.Appear(lastid, { queue:'front' })
				new Effect.Highlight(lastid, {	startcolor:highlight_start,
												endcolor:highlight_end,
												restorecolor:highlight_restore,
												})
				// Incrémenter le compteur de messages
				MSG_DIV++;
				
				// Scaling des images
				var lastimages = lastchild.getElementsByTagName('img')
				for(i = 0 ; i < lastimages.length ; i++)
				{
					// Taille de l'image
					var img = lastimages[i]
					var imgH = img.height
					var imgW = img.width
					
					// Taille de la zone d'affichage
					var sbH = shoutbox.clientHeight
					var sbW = shoutbox.clientWidth // clientWidth obligatoire, car width exprimée en % et non en pixels
										
					// Test de débordement
					if(imgH > sbH || imgW > sbW)
					{
						// Proportions
						var proH = imgH / sbH
						var proW = imgW / sbW

						// Redimentionner par la Hauteur
						if(proH > proW)
						{
							var newW = Math.round(imgW * sbH / imgH)
							var newH = sbH
						}
						// Redimentionner par la Largeur
						else
						{
							var newH = Math.round(imgH * sbW / imgW)
							var newW = sbW
						}
						// Redimentionner !
						img.style.height = newH+"px"
						img.style.width = newW+"px"
					}
				}
				// Force le rafraîchissement... sinon scriptaculous n'affiche pas tous les messages !!
				divs = shoutbox.getElementsByTagName('div')
				for (i = 0 ; i < divs.length ; i++)
				{
					if(divs[i].style.display == 'none' && divs[i] != lastchild && ( divs[i].className == "msg" || divs[i].className == "pm" || divs[i].className == "command") )
						divs[i].style.display = 'block';

					if(divs[i].id != lastid && ( divs[i].className == "msg" || divs[i].className == "pm" ) && divs[i].name != "enlighted" )
						divs[i].style.backgroundColor = bg_block

					if(divs[i].id != lastid && divs[i].className == "command" )
						divs[i].style.backgroundColor = bg_none
				}
				
				// Mettre à jour le titre de la fenêtre
				var username = lastchild.childNodes[0].childNodes[1].textContent
				document.title = "Zomb'ShoutBox ("+trim(trim(username, " >"))+")"

				// Nettoyer les DIV
				if(MSG_DIV > MSG_CEIL && MSG_REFRESH && MSG_CEIL > 0) // variables définies dans index.php
				{
					var sb = $('shoutbox');

					if ( sb.hasChildNodes() )
					    while ( sb.childNodes.length > 3 )
					        sb.removeChild( sb.firstChild )

					MSG_DIV = 0
				}
			}
			// Mise à jour du tag de protection
			alreadydone.value = lastid;
			// Scroll down du div
			shoutbox.scrollTop = shoutbox.scrollHeight
			
		    },
		});
		
	updater_sidebar = new Ajax.PeriodicalUpdater(
		"sidebartop",
		"users.php",
		{
		method: 'get',
		frequency: 5,
		decay: 1,
		evalScripts:true
		}
		)
}

//--------------
function fafficher()
{
	new Ajax.Updater(
		"shoutbox", 
		"afficher.php", 
		{
		method: 'get',
		insertion: Insertion.Bottom,
		onSuccess: function(transport){
			var lastid = $('shoutbox').lastChild.id
			var shoutbox = $('shoutbox')
			// Effets visuels
			if(alreadydone.value != lastid)
			{	
				new Effect.Appear(lastid, { queue:'front' })
			}
			// Scroll down du div
			shoutbox.scrollTop = shoutbox.scrollHeight
		    },
		});
}

function ecrire() 
{ 
	var message = $('message').value;
	if(trim(message) == '') return false;
	
	new Ajax.Updater(
		"",
		"ecrire.php",
		{
			method:'post',
			parameters: {	message:message,
							user: $('user').value
						},
			onSuccess: function(transport){
				// Recharger les onglets après la commande /auth
				if(message.substr(0,5) == "/auth")
					setTimeout("reload_after_auth()",1000*3); // en millisecondes
			}
		})
		
	fafficher()
	$('message').value = "";
}

function recherche_switch_graphique()
{
	var graph = $F('s_graphiques_graphs')
	
	new Ajax.Updater(
		"form_search_graph_container",
		"views/form_search_graph.php",
		{
			method:'post',
			parameters: {	table:graph
						}
		})
}

function recherche(form)
{
	// Patience
	$('search_results').innerHTML = "<div class=\"prefs\" style=\"text-align:center;font-size:1.1em;font-weight:bold;margin-top:1em;\">Veuillez patienter...</div>";
	$('search_results').style.display = 'block';
	
	// Ordre d'affichage
	var tri = $('s_tri_ordre').options[$('s_tri_ordre').selectedIndex].value
	
	// Commandes
	var commandes = $('s_commandes').checked
	if(commandes) commandes = 1; else commandes = 0;
	
	// Limites
	var limit = $('s_limit_value').value
	var dolimit = $('s_limit').checked
	if(dolimit) dolimit = 1; else dolimit = 0;
	
	// Salon
	var room = $('s_room').options[$('s_room').selectedIndex].value
			
	// Les différents types de recherche
	if(form == 'dates')
	{
		new Ajax.Updater(
			"search_results",
			"archives.php",
			{
				method:'post',
				parameters: {	stype:'dates',
								adebut: $('s1_adebut').value,
								tdebut: $('s1_tdebut').value,
								afin: $('s1_afin').value,
								tfin: $('s1_tfin').value,
								tri:tri,
								commandes:commandes,
								dolimit:dolimit,
								limit:limit,
								room_id:room
							}
			})
	}
	else if(form == 'user')
	{
		new Ajax.Updater(
			"search_results",
			"archives.php",
			{
				method:'post',
				parameters: {	stype:'user',
								user: $('s2_user').value,
								tri:tri,
								commandes:commandes,
								dolimit:dolimit,
								limit:limit,
								room_id:room
							}
			})
	}
	else if(form == 'fulltext')
	{
		new Ajax.Updater(
			"search_results",
			"archives.php",
			{
				method:'post',
				parameters: {	stype:'fulltext',
								text: $('s3_text').value,
								tri:tri,
								commandes:commandes,
								dolimit:dolimit,
								limit:limit,
								room_id:room
							}
			})
	}
	else if(form == 'graphiques')
	{
		var ville = $F('s_graphiques_villes')
		var graph = $F('s_graphiques_graphs')
		
		new Ajax.Updater(
			"search_results",
			"archives.php",
			{
				method:'post',
				parameters: {	stype:'graphiques',
								graphique:graph,
								ville:ville,
								tri:tri,
								commandes:commandes,
								dolimit:dolimit,
								limit:limit,
								room_id:room
							}
			})
	}
	else if(form == 'citoyens')
	{
		var ville = $F('s_citoyens_ville')
		
		new Ajax.Updater(
			"search_results",
			"archives.php",
			{
				method:'post',
				parameters: {	stype:'citoyens',
								ville:ville,
								tri:tri,
								commandes:commandes,
								dolimit:dolimit,
								limit:limit,
								room_id:room
							}
			})
	}
	else if(form == 'chantiers')
	{
		var chantier = $F('s_chantiers')
		
		new Ajax.Updater(
			"search_results",
			"views/recherche/view_search_chantiers.php",
			{
				method:'post',
				parameters: {
								chantier:chantier
							}
			})
	}
	else if(form == 'objets')
	{
		var objet = $F('s_objets')
		
		new Ajax.Updater(
			"search_results",
			"views/recherche/view_search_objets.php",
			{
				method:'post',
				parameters: {
								objet:objet
							}
			})
	}
	else if(form == 'chantiers_all')
	{		
		new Ajax.Updater(
			"search_results",
			"views/recherche/view_search_chantiers.php",
			{
				method:'post',
				parameters: {
								all:true
							}
			})
	}
	else if(form == 'objets_all')
	{		
		new Ajax.Updater(
			"search_results",
			"views/recherche/view_search_objets.php",
			{
				method:'post',
				parameters: {
								all:true
							}
			})
	}
	
	// Masquage des formulaires pour libérer de la place
	$('search_forms').innerHTML = "<div style=\"text-align:center;\"><input type=\"submit\" onclick=\"ajaxrefresh('tab_search')\" id=\"tab_search_rb\" value=\"&#x232B; Revenir au formulaire de recherche\" /></div>"
	
}

function reload_after_auth()
{	
	new Ajax.Updater(
		"tab_mp",
		"views/tab_mp.php"
		)
		
	new Ajax.Updater(
		"tab_today",
		"views/tab_today.php"
		)
		
	new Ajax.Updater(
		"tab_notes",
		"views/tab_notes.php"
		)		
}

function enterk(evt)
{
	if (window.event) code = evt.keyCode; // IE - Safari
	else if (evt.which) code = evt.which; // Firefox
	
	if(code == 13)
	{
		ecrire()
	}
}

function enlight(user,light)
{
	var divs = document.getElementsByTagName('div')
	
	for (i = 0 ; i < divs.length ; i++)
		if( divs[i].className == "msg" || divs[i].className == "pm" )
			if(divs[i].lastChild.childNodes[1].childNodes[0].innerHTML == user)
				if(light)
				{
					divs[i].style.backgroundColor = highlight_end
					divs[i].name = "enlighted";
				}
				else
				{
					divs[i].style.backgroundColor = highlight_restore
					divs[i].name = "normal";
				}
}

function endthreads()
{
	// Arrêter les updaters ajax
	updater_twitter.stop()
	updater_citations.stop()
	updater_sondages.stop()
	updater_cronusers.stop()
	updater_calendrier.stop()
	//updater_notes.stop()
	updater_today.stop()
	updater_mp.stop()
	updater_xml.stop()
	updater_shoutbox.stop()
	updater_sidebar.stop()
}

function deco()
{
	endthreads()
	// Rediriger
	window.location = "deco.php"
}

function quit()
{
	endthreads()
	new Ajax.Updater("","deco_soft.php")
}

function setfocus(focus)
{
		
	if(focus)
	{
		new Ajax.Updater(
			"",
			"focus.php",
			{
				method:'post',
				parameters: {	focus:'1' }
			})
	}
	else
	{
		new Ajax.Updater(
			"",
			"focus.php",
			{
				method:'post',
				parameters: {	focus:'0' }
			})
	}
}

function input(txt)
{		
	// Envoyer le message
	$('message').value = "/"+txt;
	ecrire();
	
	// Cacher les commmandes
	showhide('commandes','cmdbt')
}

function showhide(element,bouton)
{
	var divcom = $(element)
	var cmdbt = $(bouton)
	
	if(divcom.style.display == 'block')
	{
		divcom.style.display = 'none'
		cmdbt.style.backgroundColor = bg_none
	}
	else
	{
		divcom.style.display = 'block'
		cmdbt.style.backgroundColor = bg_block
	}
}

function insertcolor()
{	
	var couleur = $('jscolorpicker').value
	var appli = $('colorappli').value
	
	if(appli == "fg")
		appli = "couleur="
	else
		appli = "fond="
		
	couleur = "#"+couleur	
	
	var message = $('message')
	
	var sel = new SelectedText(message)
	var selected = sel.get().text
	var before = sel.getBefore().text
	var after = sel.getAfter().text
	
	message.value = before+appli+couleur+"("+selected+")"+after	
	
	showhide('colorpicker','f_color')
}

function tformat(option)
{	
	var message = $('message')
	
	var sel = new SelectedText(message)
	var selected = sel.get().text
	var before = sel.getBefore().text
	var after = sel.getAfter().text
	
	var item = "";
	switch(option)
	{
		case "i": item = "*"; break;
		case "b": item = "**"; break;
		case "u": item = "_"; break;
		case "s": item = "__"; break;
		default: item=""; break;
	}
	
	message.value = before+item+selected+item+after
	
}

function mp(user)
{
	var message = $('message')
	message.value = "/w "+user+" "
	message.focus()
}

function commandesmenu()
{
	var message = $('message')
		
	var context_menu = new Control.ContextMenu(document.body ,{ leftClick: false  });  
	// Identifier son pseudo 
	context_menu.addItem({  
	     label: 'Identification',  
	     callback: function(){  
	        message.value = "/auth <mot de passe>"
			message.focus()
	     }, enabled:true
	 });
	// Emote
	 context_menu.addItem({  
	     label: 'Emote',  
	     callback: function(){  
			message.value = "/me <texte>"
			message.focus()
	     }, enabled:true
	 });
	// Envoyer un MP
	 context_menu.addItem({  
	     label: 'Message privé',  
	     callback: function(){  
			message.value = "/w <pseudo> <texte>"
			message.focus()
	     }, enabled:true
	 });
	// Prendre une note
	 context_menu.addItem({  
	     label: 'Prendre une note',  
	     callback: function(){  
			message.value = "/note <texte>"
			message.focus()
	     }, enabled:true
	 });
	// Whois citoyen
	 context_menu.addItem({  
	     label: '<img src="smilies/h_ghost.gif" />&nbsp;Informations sur un citoyen',  
	     callback: function(){  
			message.value = "/whois <pseudo>"
			message.focus()
	     }, enabled:true
	 });
	// Position d'un citoyen
	 context_menu.addItem({  
	     label: '<img src="smilies/h_ghost.gif" />&nbsp;Localiser un citoyen',  
	     callback: function(){  
			message.value = "/pos <pseudo>"
			message.focus()
	     }, enabled:true
	 });
	// Qui est vivant ?
	context_menu.addItem({  
	     label: '<img src="smilies/h_human.gif" />&nbsp;Qui est vivant ?',  
	     callback: function(){  
			message.value = "/who vivant"
			ecrire()
	     }, enabled:true
	 });
	// Qui est mort ?
	context_menu.addItem({  
	     label: '<img src="smilies/h_death.gif" />&nbsp;Qui est mort ?',  
	     callback: function(){  
			message.value = "/who mort"
			ecrire()
	     }, enabled:true
	 });
	// Qui est banni ?
	context_menu.addItem({  
	     label: '<img src="smilies/h_ban.gif" />&nbsp;Qui est banni ?',  
	     callback: function(){  
			message.value = "/who banni"
			ecrire()
	     }, enabled:true
	 });
	// Qui est héro ?
	context_menu.addItem({  
	     label: '<img src="smilies/r_heroac.gif" />&nbsp;Qui est héro ?',  
	     callback: function(){  
			message.value = "/who hero"
			ecrire()
	     }, enabled:true
	 });
	// Qui est gardien ?
	context_menu.addItem({  
	     label: '<img src="smilies/h_guard.gif" />&nbsp;Qui est gardien ?',  
	     callback: function(){  
			message.value = "/who gardien"
			ecrire()
	     }, enabled:true
	 });
	// Qui est fouineur ?
	context_menu.addItem({  
	     label: '<img src="smilies/h_collec.gif" />&nbsp;Qui est fouineur ?',  
	     callback: function(){  
			message.value = "/who fouineur"
			ecrire()
	     }, enabled:true
	 });
	// Qui est éclaireur ?
	context_menu.addItem({  
	     label: '<img src="smilies/h_ranger.gif" />&nbsp;Qui est éclaireur ?',  
	     callback: function(){  
			message.value = "/who eclaireur"
			ecrire()
	     }, enabled:true
	 });
	// Qui est en ville ?
	context_menu.addItem({  
	     label: '<img src="smilies/h_door.gif" />&nbsp;Qui est en ville ?',  
	     callback: function(){  
			message.value = "/who enville"
			ecrire()
	     }, enabled:true
	 });
	// Qui est dans le désert ?
	context_menu.addItem({  
	     label: '<img src="smilies/r_explor.gif" />&nbsp;Qui est dans le désert ?',  
	     callback: function(){  
			message.value = "/who dehors"
			ecrire()
	     }, enabled:true
	 });
	// Eau
	context_menu.addItem({  
	     label: '<img src="smilies/h_well.gif" />&nbsp;Réserves d\'eau de la ville',  
	     callback: function(){  
			message.value = "/ville eau"
			ecrire()
	     }, enabled:true
	 });
	// Attaque
	context_menu.addItem({  
	     label: '<img src="smilies/r_dcity.gif" />&nbsp;Estimation de l\'attaque',  
	     callback: function(){  
			message.value = "/ville attaque"
			ecrire()
	     }, enabled:true
	 });
	// Expes
	context_menu.addItem({  
	     label: '<img src="smilies/r_explor.gif" />&nbsp;Liste des expéditions',  
	     callback: function(){  
			message.value = "/expe"
			ecrire()
	     }, enabled:true
	 });
	// Chantiers
	context_menu.addItem({  
	     label: '<img src="smilies/h_door.gif" />&nbsp;Informations sur un chantier',  
	     callback: function(){  
			message.value = "/chantier <chantier>"
			message.focus()
	     }, enabled:true
	 });
	// Objet
	context_menu.addItem({  
	     label: '<img src="smilies/h_collec.gif" />&nbsp;Informations sur un objet',  
	     callback: function(){  
			message.value = "/objet <objet>"
			message.focus()
	     }, enabled:true
	 });
}

function addmenu(item)
{
	var user = item.lastChild.childNodes[1].childNodes[0].innerHTML; 
	var msg = item.lastChild.lastChild.innerHTML;
	var date = item.lastChild.childNodes[0].innerHTML;
	var citation = "@<i>"+user+" > "+msg+"</i> | "
		
	var context_menu = new Control.ContextMenu(item ,{ leftClick: true  });  
	 context_menu.addItem({  
	     label: 'Envoyer un message privé à '+user,  
	     callback: function(){  
	          mp(user)
	     }, enabled:true
	 });  
	 context_menu.addItem({  
	     label: 'Prendre ce message en note',  
	     callback: function(){  
	        
			var message = $('message')
			message.value = "/note "+msg
			ecrire()
			
	     }, enabled:true
	 });
	 context_menu.addItem({  
	     label: 'Citer ce message',  
	     callback: function(){  
	        
			var message = $('message')
			message.value = citation
			message.focus()
			
	     }, enabled:true
	 });
}

function newexpe()
{
	var chk = document.getElementsByName('expe_users')
	
	var users = ""
	for(i = 0 ; i < chk.length ; i++)
		if(chk[i].checked)
			users += chk[i].value + ","
	
	var trajet = $('expe_trajet').options[$('expe_trajet').selectedIndex].value
	var depart_date = $('expe_depart_date').value
	var depart_time = $('expe_depart_time').value	
	var arrivee_time = $('expe_arrivee_time').value
		
	new Ajax.Updater(
		"experesponse",
		"controllers/controller_calendrier.php",
		{
			method:'post',
			parameters: {	a:'newexpe',
							trajet:trajet,
							depart_date:depart_date,
							depart_time:depart_time,
							arrivee_time:arrivee_time,
							participants:users
						}	,
				onSuccess: function(transport){
					new Ajax.Updater(
						"innertab_cal_4",
						"views/calendrier/view_cal_expeditions.php",
						{
							evalScripts:true
						}
						)
				}
		})
}

function delexpe(id)
{
	new Ajax.Updater(
		"experesponse",
		"controllers/controller_calendrier.php",
		{
			method:'post',
			parameters: {	a:'delexpe',
							id:id,
						}	,
				onSuccess: function(transport){
					new Ajax.Updater(
						"innertab_cal_4",
						"views/calendrier/view_cal_expeditions.php",
						{
							evalScripts:true
						}
						)
				}
		})
}

function newevent()
{	
	new Ajax.Updater(
		"formresponse",
		"controllers/controller_calendrier.php",
		{
			method:'post',
			parameters: {	a:'new',
							sdate:$('sdate').value,
							edate:$('edate').value,
							stime:$('stime').value,
							etime:$('etime').value,
							action:$('action').value
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"innertab_cal_2",
					"views/calendrier/view_cal_owndispo.php",
					{
					evalScripts:true
					}
					)
			}
		})
}

function quickevent(prop)
{
	if(prop != 'ajd' && prop != 'demain') return false;
	
	new Ajax.Updater(
		"formresponse",
		"controllers/controller_calendrier.php",
		{
			method:'post',
			parameters: {	a:'newquick',
							date:prop,
							action:$('action_'+prop).value
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"innertab_cal_2",
					"views/calendrier/view_cal_owndispo.php",
					{
					evalScripts:true
					}
					)
			}
		})
}

function delevent(delid)
{
	new Ajax.Updater(
		"formresponse",
		"controllers/controller_calendrier.php",
		{
			method:'post',
			parameters: {	a:'del',
							id:delid
						},
			onSuccess: function(transport){
				new Ajax.Updater(
					"innertab_cal_2",
					"views/calendrier/view_cal_owndispo.php",
					{
					evalScripts:true
					}
					)
			}
		})
}

// thanks to http://stackoverflow.com/questions/557914/how-can-i-highlight-a-subset-of-the-text-in-an-input-box
function SelectedText(input) {
  // Replace the currently selected text with the given value.
  this.replace = function(text) {
    var selection = this.get();

    var pre = input.value.substring(0, selection.start);
    var post = input.value.substring(selection.end, input.value.length);

    input.value = pre + text + post;

    this.set(selection.start, selection.start + text.length);

    return this;
  }

  // Set the current selection to the given start and end points.
  this.set = function(start, end) {
    if (input.setSelectionRange) {
      // Mozilla
      input.focus();
      input.setSelectionRange(start, end);
    } else if (input.createTextRange) {
      // IE
      var range = input.createTextRange();
      range.collapse(true);
      range.moveEnd('character', end);
      range.moveStart('character', start);
      range.select();
    }

    return this;
  }

  // Get the currently selected region.
  this.get = function() {
    var result = new Object();

    result.start = 0;
    result.end = 0;
    result.text = '';

    if (input.selectionStart != undefined) {
      // Mozilla
      result.start = input.selectionStart;
      result.end = input.selectionEnd;
    } else {
      // IE
      var bookmark = document.selection.createRange().getBookmark()
      var selection = inputBox.createTextRange()
      selection.moveToBookmark(bookmark)

      var before = inputBox.createTextRange()
      before.collapse(true)
      before.setEndPoint("EndToStart", selection)

      result.start = before.text.length;
      result.end = before.text.length + selection.text.length;
    }

    result.text = input.value.substring(result.start, result.end);

    return result;
  }


	// Ajout : le texte avant la sélection
	this.getBefore = function() {
    var result = new Object();

    result.start = 0;
    result.end = 0;
    result.text = '';

    if (input.selectionStart != undefined) {
      // Mozilla
      result.start = input.selectionStart;
    } else {
      // IE
      var bookmark = document.selection.createRange().getBookmark()
      var selection = inputBox.createTextRange()
      selection.moveToBookmark(bookmark)

      var before = inputBox.createTextRange()
      before.collapse(true)
      before.setEndPoint("EndToStart", selection)

      result.start = before.text.length;
      result.end = before.text.length + selection.text.length;
    }

    result.text = input.value.substring(0, result.start);

    return result;
  }

	// Ajout : le texte avant la sélection
	this.getAfter = function() {
	  var result = new Object();

	  result.start = 0;
	  result.end = 0;
	  result.text = '';

	  if (input.selectionStart != undefined) {
	    // Mozilla
		result.end = input.selectionEnd;
	  } else {
	    // IE
	    var bookmark = document.selection.createRange().getBookmark()
	    var selection = inputBox.createTextRange()
	    selection.moveToBookmark(bookmark)
	
	    var before = inputBox.createTextRange()
	    before.collapse(true)
	    before.setEndPoint("EndToStart", selection)

	    result.end = before.text.length + selection.text.length;
	  }

	  result.text = input.value.substring(result.end, input.value.length);

	  return result;
	}


}


// Fonctions trim : http://www.webtoolkit.info/javascript-trim.html
function trim(str, chars) {
	return ltrim(rtrim(str, chars), chars);
}
 
function ltrim(str, chars) {
	chars = chars || "\\s";
	return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
}
 
function rtrim(str, chars) {
	chars = chars || "\\s";
	return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
}
