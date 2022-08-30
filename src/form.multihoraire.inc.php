
    <div id="multihoraire"></div>

<?php return ; ?>
<div class="table-responsive">
	<table class="table dates">
        <thead>
            <tr>
                <th>Dates</th>
                <th>Type</th>
                <th>Jours</th>
                <th>Horaires</th>
            </tr>
        </thead>
        <tbody>
            <?php for ( $i = 0 ; $i < 10 ; $i++ ) { ?>
            <tr>
                <th>
                <input class="form-control date" type="date" min="<?php date('Y-m-d') ; ?>" name="date[<?php echo $i ; ?>][debut]" value="<?php echo htmlentities(@$post['date'][$i]['debut']) ; ?>" placeholder="jj/mm/aaaa" required="required" autocomplete="chrome-off" />
                <input class="form-control date" type="date" min="<?php date('Y-m-d') ; ?>" name="date[<?php echo $i ; ?>][fin]" value="<?php echo htmlentities(@$post['date'][$i]['debut']) ; ?>" placeholder="jj/mm/aaaa" required="required" autocomplete="chrome-off" />
                </th>
                <td>
                    <select class="form-control" name="">
                    <option value="opening">Ouverture</option>
                    <option value="last_entry">Dernière entrée</option>
                    <option value="ceremony">Horaires de cérémonie</option>
                    <option value="guided_tour">Horaires de visite guidée</option>
                    <option value="departure">Horaires de départ</option>
                    <option value="representation">Horaires de représentation</option>
                    </select>
                </td>
                <td>
                    <?php
                        $jours = ['MON' => 'Lundi','TUE' => 'Mardi', 'WED' => 'Mercredi', 'THU' => 'Jeudi', 'FRI' => 'Vendredi', 'SAT' => 'Samedi', 'SUN' => 'Dimanche'] ;
                        foreach ( $jours as $k => $lib ) {
                            ?>
                            <input type="checkbox" class="btn-check" id="<?php echo $k ;?>" autocomplete="off">
                            <label class="btn btn-secondary" for="<?php echo $k ; ?>"><?php echo $lib ; ?></label>
                            <?php
                        }
                    ?>
                </td>
                <td>
                <input class="form-control time" type="time" name="date[' . $i . '][hfin]" value="' . htmlentities(@$post['date'][$i]['hfin']) . '" placeholder="hh:mm" />
                <input class="form-control time" type="time" name="date[' . $i . '][hfin]" value="' . htmlentities(@$post['date'][$i]['hfin']) . '" placeholder="hh:mm" />
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>