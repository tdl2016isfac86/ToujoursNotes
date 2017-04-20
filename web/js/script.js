
// Sélection du formulaire

$(document).ready(function () {

	var editOnClick = function() {
		var id = $(this).parent().parent().attr('id').substr(1);
		var titre = $('#n'+id+' .title').html();
		var contenu = $('#n'+id+' p').html();

		$('#formAjout').toggle(false);
		$('#formEdit').toggle(true);

		$('#formEdit input[name=title]').val(titre);
		$('#formEdit textarea').val(contenu);
		$('#formEdit input[name=id]').val(id);
	};

	var deleteOnClick = function() {
		var id = $(this).parent().parent().attr('id').substr(1);
		if(confirm('Êtes-vous sûr de vouloir supprimer cette note ?')) {
			$.ajax({
				url : 'ajaxSuppr',
				method : 'DELETE',
				data : {
					id : id
				},
				success : function (data) {
					// data = les données retournées par Symfony (new Response(....))
					if (data == 'ok') {
							$('#n'+id).remove();
					}
				}
			});
		}
	};

	$('#formEdit').toggle(false);

	$('#formAjout').submit(function() {
		var titre = $("#formAjout input[name='title']")[0].value;
		var contenu = $('#formAjout textarea')[0].value;
		
		if(titre.trim() != '') {
			$.ajax({
				url: 'ajaxajout',
				method: 'POST',
				data: {
					title : titre,
					content : contenu
				},
				success: function(data) {
					var obj = JSON.parse(data);
					var divNote = '<div class="note" id="n'+obj.id+'">'
					+'<a href="/'+ obj.id +'/show" class="title">'+titre+'</a>'
				    + '<p>'+contenu+'</p>'
				    +'<div>'
				    +'<span class="date">'+ obj.date +'</span>'
				    +'<span class="delete">del</span>'
					+'<span class="edit">edit</span>'
				    +'</div>'
					+'</div>';

					$('#liste').prepend(divNote);
					$("input[name='title']")[0].value = '';
					$('textarea')[0].value = '';

					$('#n'+obj.id+' .title').click(editOnClick);
					$('#n'+obj.id+' .edit').click(editOnClick);
					$('#n'+obj.id+' .delete').click(deleteOnClick);
				}
			});
		}
		else {
			alert("Il est nécessaire d'ajouter un titre !!");
		}
		return false;
	});

	$('#formEdit').submit(function() {
		var titre = $("#formEdit input[name='title']")[0].value;
		var contenu = $('#formEdit textarea')[0].value;
		var id = $("#formEdit input[name='id']")[0].value;

		if(titre.trim() != '') {
			$.ajax({
				url: 'ajaxupdate',
				method: 'POST',
				data: {
					title: titre,
					content: contenu,
					id: id
				},
				success: function(data) {
					var obj = JSON.parse(data);
					$('#formEdit').toggle(false);
					$('#formAjout').toggle(true);

					$('#n'+id+' .title').text(titre);
					$('#n'+id+' p').text(contenu);
					$('#n'+id+' .date').text(obj.date);

				}
			});
		}
		return false;
	});

	$('.title').click(editOnClick);	
	$('.edit').click(editOnClick);
	$('.delete').click(deleteOnClick);

	$('#formEdit button').click(function() {
		$('#formEdit').toggle(false);
		$('#formAjout').toggle(true);
		return false;
	});
	
	$( function() {
	    $( "#liste" ).sortable({
	      connectWith: "#liste",
	      handle: ".title",
	      cancel: ".note-toggle",
	      placeholder: "note-placeholder ui-corner-all",
	      update: function(event, ui) {
	      	//ui.item c'est l'élément qu'on vient de déplacer, donc mon point de départ
	      	var depart = ui.item.attr('data-sequence');
	      	var next = ui.item.next().attr('data-sequence');
	      	var prev = ui.item.prev().attr('data-sequence');
	      	if(depart < next) {
	      		var arrive = next;
	      	}
	      	else {
	      		var arrive = prev;
	      	}
      		$.ajax({
				url: 'ajaxresequence',
				method: 'POST',
				data: {
					depart: depart,
					arrive: arrive,
				},
				success: function(data) {
					var listItems = [];
			        if(depart > arrive) {
			            for(var i=arrive; i<depart;i=i+1) {
		                // On va chercher l'element concerné et onl'ajoute au tableau
			            	listItems.push($('[data-sequence='+i+']'));
			            	console.log(i);
			            }
			            $('[data-sequence='+depart+']').attr('data-sequence',arrive);
		                // On modifie son data-sequence (i+1)
			            listItems.forEach(function(item) {
		            		var ds = item.attr("data-sequence");
		            		item.attr("data-sequence",parseInt(ds)+1)
			            });
			        }
			        else {
			            for(var i=arrive; i>depart;i=i-1) {
		                // On va chercher l'element concerné et onl'ajoute au tableau
			            	listItems.push($('[data-sequence='+i+']'));
			            	console.log(arrive, depart, i);
			            }
			            $('[data-sequence='+depart+']').attr('data-sequence',arrive);
			            // On modifie son data-sequence (i-1)
			            listItems.forEach(function(item) {
		            		var ds = item.attr("data-sequence");
		            		item.attr("data-sequence",parseInt(ds)-1)
			            });
			        }
					console.log(listItems);
				}
			});
	    }
	});
	 
	    $( ".note" )
	      .addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
	      .find( ".title" )
	        .addClass( "ui-widget-header ui-corner-all" )
	        .parent().prepend( "<span class='ui-icon ui-icon-minusthick note-toggle'></span>");
	 
	    $( ".note-toggle" ).on( "click", function() {
	      var icon = $( this );
	      icon.toggleClass( "ui-icon-minusthick ui-icon-plusthick" );
	      icon.closest( ".note" ).find( ".title" ).toggle();
	    });
	});
});

