
					<div class="alert alert-success" role="alert">
						<h3>Membres sur lesquels on peut saisir :</h3>

						<p>Les membres qui n'ont pas de projet d'écriture individuel renseigné doivent être <a href="https://base.apidae-tourisme.com/diffuser/projet/2792?25" target="_blank">abonnés au projet d'écriture multi-membre ApidaeEvent</a>.</p>
					
						<table class="table">
							<thead>
								<tr>
									<th>ID</th>
									<th>Nom</th>
									<th>Terr. ou COM</th>
									<th>Mails alertés</th>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ( $configApidaeEvent['membres'] as $membre )
								{
									echo '<tr>' ;
										echo '<th>' ;
											echo '<a href="'.$pma->url_base().'/echanger/membre-sitra/'.$membre['id_membre'].'" target="_blank">'.$membre['id_membre'].'</a> ' ;
										echo '</th>' ;
										echo '<th>' ;
											echo '<a href="'.$pma->url_base().'/echanger/membre-sitra/'.$membre['id_membre'].'" target="_blank">'.$membre['nom'].'</a> ' ;
											echo '<br />' ;
											echo '&rsaquo; <a href="'.$pma->url_base().'/echanger/membre-sitra/'.$membre['site'].'" target="_blank">'.$membre['site'].'</a> ' ;
										echo '</th>' ;
										echo '<td>' ;
											if ( @$membre['id_territoire'] !== null )
												echo 'TERR. : <a href="'.$pma->url_base().'/consulter/objet-touristique/'.$membre['id_territoire'].'" target="_blank">'.$membre['id_territoire'].'</a>' ;
											elseif ( @$membre['id_commune'] !== null )
												echo 'COM. : '.$membre['id_commune'] ;
											else
												echo '<strong style="color:red;">Non renseignée</strong>' ;
										echo '</td>' ;
										echo '<td>' ;
											if ( is_array($membre['mail']) ) echo implode(', ',$membre['mail']) ; else echo $membre['mail'] ;
										echo '</td>' ;
									echo '</tr>' ;
								}
								?>
							</tbody>
						</table>

					</div>

