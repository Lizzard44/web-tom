<?php
    require_once('includes/bootstrap.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>WebTOM - Web-based Tune-O-Matic (powered by 2eck)</title>
        <link rel="stylesheet" href="css/ui-darkness/jquery-ui-1.8.4.custom.css" type="text/css" />
        <link rel="stylesheet" href="css/webtom.css" type="text/css" />
        <script type="text/javascript" src="js/jquery-1.4.2.min.js">
        </script>
        <script type="application/javascript" src="js/jquery-ui-1.8.4.custom.min.js">
        </script>
    </head>
    <body>
        <div id="header">
        </div>
        <script type="text/javascript">
            window.filepath = '';

            var presetMap = <?php echo json_encode(_PRESET_MAPPING())?>;

            function updatePresetString(elem){
				for(var i in presetMap){
					if(elem.id==presetMap[i]){
						var val = $('#presetvalue').val();
						var presetValue = elem.value.replace(elem.id, '');
						var newVal = val.substr(0, i) + presetValue + val.substr(parseInt(i)+parseInt(presetValue.length));
						$('#presetvalue').val(newVal);
					}
				}
            }

			function applyPresetValue(){
				var val = $('#presetvalue').val();
				if(val.length != presetMap.length){
					alert('Der Preset String passt nicht zum aktuellen Satz an Patches!');
					return;
				}
				for(var i in presetMap){
					var valElem = $('#'+presetMap[i]+val.substr(i,1));
					var setValue = val.substr(i,1);
					if(valElem.length == 0){
						alert('Der Wert "'+setValue+'" für Patch '+presetMap[i]+' existiert nicht. Es wird stattdessen der Original Patch mit der ID 0 verwendet!');
					}
					$('#'+presetMap[i]).val(presetMap[i]+setValue);
				}
			}

            $(function(){
                $("#tabs").tabs({
                    ajaxOptions: {
                        error: function(xhr, status, index, anchor){
                            $(anchor.hash).html("Tab konnte nicht geladen werden..");
                        }
                    }
                });
                $('#disclaimerDialog').dialog({
                    autoOpen: false,
                    minWidth: 450,
                    resizable: false,
                    modal: true
                });
                $('#downloadDialog').dialog({
                    autoOpen: false,
                    resizable: false,
                    modal: true,
                    minWidth: 450
                });
                <?php if(_DEBUG): ?>
                    $('#debugDialog').dialog({
                        autoOpen: true,
                        modal: false,
                        draggable: true,
                        resizable: false,
                        minWidth: 400
                    });
                <?php endif; ?>
                $("button").button();
                $("#downloadButton").button();
            });

            var uploadCallback = function(message, isError){
                if (isError == 1) {
                    alert('ERROR: ' + message);
                } else {
                    alert(message);
                }

            }

            var analyzerCallback = function(){
            	$("#eepromOutput").html(document.getElementById('uploadFrame').contentDocument.getElementById("analysisOutput").innerHTML);
            	$("#analyticalOutput").show();
            }

            var showDownloadInfo = function(filepath, filename, md5){
//                alert(filepath+';'+filename+';'+md5);
                window.filepath = filepath;
                $('#md5').html(md5);
                $('#filename').html(filename);
                $('#downloadlink').html('<a href="'+filepath+'">Click</a>');
                $('#downloadDialog').dialog('open');
                $('#downloadButton').blur();
                $('#downloadButton').button('refesh');
            }

            var showDisclaimer = function(){
                $('#disclaimerDialog').dialog('open');
                $('#acceptButton').blur();
                $('#acceptButton').button('refesh');
                $('#acceptButton').blur();
                $('#cancelButton').button('refesh');
            }
        </script>
        <div id="tabs">
            <ul>
                <li>
                    <a href="#tabs-1">File Patcher</a>
                </li>
                <li>
                    <a href="#tabs-eeprom">EEPROM Analyzer</a>
                </li>
                <li>
                    <a href="#tabs-2">Patch Creator</a>
                </li>
                <li>
                    <a href="#tabs-3">Über WebTune-O-Matic</a>
                </li>
            </ul>
            <div id="tabs-1" class="tabContent">
                <form enctype="multipart/form-data" method="post" action="ajax/createpatch.php" id="patchCreatorForm" target="uploadFrame">
                    <fieldset>
                        <legend>Quelldateien</legend>
                        <div class="singleMap">
                            <label for="original">Original File (371568):</label><input type="file" name="original" />
                        </div>
                        <div class="singleMap">
                            <label for="source">Datei zum Patchen (Basis 371568):<br /><span class="labelInfo">Wenn leergelassen, wird Originaldatei verwendet.</span></label><input type="file" name="source" />
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend>Preset</legend>
                        <div class="singleMap">
                            <label for="presetvalue">Aktueller Preset-String:<br /><span class="labelInfo">Ändern des Strings ändert auch die aktuelle Zusammenstellung!</span></label>
                            <input type="text" name="presetvalue" id="presetvalue" onchange="applyPresetValue();" value="<?php echo str_pad('', count(PatchLocator::getMaps('R60_2005')), '0')?>" />
                        </div>
                    </fieldset>
                    <?php

                        $mapGroups = PatchLocator::getMapsGrouped('R60_2005');

                        /* @var Map $map */
                        foreach($mapGroups as $groupname => $maps){
                    ?>
                    <fieldset>
                    <?php
                        $groupname = htmlentities($groupname);
                        if($groupname != 'none'){
                           ?>
                            <legend><?php echo $groupname; ?></legend>
                           <?php
                        }
                    ?>
                    <?php
                        foreach($maps as $map){
                            $settings = $map->getSettings();
                            ?>
                        <div class="singleMap"><label
                            for="setting[<?php echo $map->getAbbreviation()?>]"><?php echo htmlentities(reset($settings)->getDescription())?>:</label><select onchange="updatePresetString(this);"
                            name="setting[<?php echo $map->getAbbreviation()?>]" id="<?php echo $map->getAbbreviation()?>">
                            <?php
                                foreach($settings as $settingName => $setting){
                                            ?>
                                            <option value="<?php echo $settingName ?>" id="<?php echo $settingName; ?>"><?php echo htmlentities($setting->getListEntry())?></option>
                                            <?php
                                }
                            ?>
                            </select>
                        </div>
                    <?php
                    }
                    ?>
                    </fieldset>
                    <?php
                }

                ?>
                <script type="text/javascript">
                    $(function() {
                        $("button").button();
                    });
                </script>
                <div id="buttonFooter" class="buttonFooter">
                    <div style="float: left;">
                        <button onclick="showDisclaimer();return false;">Tuning File erstellen</button>
                    </div>
                    <div style="float: left;margin-left: 30px;">
                        <label for="overwriteOriginal"><input type="checkbox" id="overwriteOriginal" name="overwriteOriginal" value="1" /> Auf original stehendes mit Original überschreiben</label>
                    </div>
                </div>
                </form>
            </div>
            <div id="tabs-2">
                n/a
            </div>
            <div id="tabs-eeprom" class="tabContent">
                <form enctype="multipart/form-data" method="post" action="ajax/analyzeeeprom.php" id="eepromAnalyzerForm" target="uploadFrame">
                    <fieldset>
                        <legend>Quelldatei</legend>
                        <div class="singleMap">
                            <label for="eeprom">EEPROM Dump:</label><input type="file" name="eeprom" />
                        </div>
                    </fieldset>
                    <div class="buttonFooter">
                        <button>EEPROM Dump analysieren</button>
                    </div>
                </form>
                <fieldset id="analyticalOutput" style="display: none;">
                    <legend>Ausgabe</legend>
                    <div id="eepromOutput">
                    </div>
                </fieldset>
            </div>
            <div id="tabs-3" class="tabContent">
                <fieldset>
                <img src="img/logo.png" style="float: left; margin-top: 15px;" />
                <div style="float: left;margin-left: 10px;">
                    <h2>Versionsinfo</h2>
                    <p>
                        WebTune-O-Matic, web-tom<br /><strong>Version <?php echo _VERSION; ?> (Rev. <?php echo _REVISION; ?>)</strong><br /><br />
                        Projektseite: <a href="http://code.google.com/p/web-tom/" target="_blank">http://code.google.com/p/web-tom/</a><br />
                        <br />
                        Besonderer Dank gehen an Thomas "2Eck" Drechsler und LiZZard
                    </p>
                </div>
                </fieldset>
                <h2 style="clear: left;">Über</h2>
                <p>Der WebTune-O-Matic ist eine web-basierte Version des Tune-O-Matics von Thomas "2eck" Drechsler.</p>
                <p>WebTOM benötigt die gleichen .2PF-Patchfiles, wie sie auch der Tune-O-Matic verwendet.
                </p>
                <p>Zudem wird ein Originaldatenstand des jeweiligen Steuergerätes benötigt, welcher aus
                urheberrechtlichen Gründen nicht mitgeliefert werden darf.</p>
                <p>
                    2PF-Files ab TOM Version 1.08 sind mit dem WebTOM getestet und verifiziert!
                </p>
                <p>
                    <strong>WARNUNG:</strong> Auch wenn - oder gerade weil - der WebTOM eine einfache "Klickibunti"
                    Oberfläche ist, um Tuningfiles zusammenzubauen, sollte der Benutzer ganz genau wissen, was er da
                    tut. Es gibt keine Plausibilitätsprüfung, welche unterschiedlichste Kennfeldkombinationen auf
                    deren garantierte Kompatibilität prüft.
                </p>

                <h2>Haftungsausschluss</h2>
                <p>
                    <strong>WARNUNG:</strong> Das erstellte Tuningfile hat keine korrekten Checksummen!
                    Falls das File ohne nachfolgende Korrektur der Checksummen in das Motorsteuergerät
                    geschrieben wird, wird das Steuergerät das File nicht anerkennen!!!
                    Das Auto wird NICHT MEHR STARTEN und das Motorsteuergerät muss wiederbelebt oder
                    ausgetauscht werden!!!
                </p>
                <p>
                    Das erstellte Tuningfile dient ausschliesslich Demonstrationszwecken und ist keinesfalls
                    dazu bestimmt in ein Motorsteuergerät geschrieben und in Betrieb genommen zu werden!!!
                </p>
                <p>
                    Dem Benutzer ist bekannt, dass der Einsatz eines erstellten Tuningfiles zum Erlöschen
                    der allgemeinen Betriebserlaubnis führt. Dies hat die Konsequenz, dass das so veränderte
                    Fahrzeug nicht im öffentlichen Straßenverkehr benutzt werden darf. Ebenfalls ist dem
                    Benutzer bekannt, dass ohne allgemeine Betriebserlaubnis auch kein Versicherungsschutz besteht
                    und sich durch das Abrufen der Mehrleistung der Verschleiß der Fahrzeugteile erhöht.
                </p>
                <p>
                    Der Autor dieses Programms haftet nicht für Schäden, die beim Fahrzeugbetrieb eines durch
                    ein hier erstelltes Tuningfile veränderten Fahrzeuges ohne Allgemeine Betriebserlaubnis
                    und/oder ohne Versicherungsschutz entstehen.
                </p>
                <p>
                    Durch das Aufspielen eines Tuningfiles erlöschen Herstellergarantie und jegliche Gewährleistungsansprüche
                    in Bezug auf das veränderte Fahrzeug. Hierfür kann der Autor dieser Software nicht haftbar gemacht werden!
                </p>
                <p>
                    Mit dem Erstellen eines Tuningfiles und dem Akzeptieren des Haftungsausschluss, bestätigt der Benutzer
                    dieses Programms den oben genannten Verwendungszweck des erstellten Tuningfiles.
                </p>
            </div>
        </div>
        <div id="disclaimerDialog" title="Haftungsausschluss">
            <p>
                <strong>WARNUNG:</strong> Das erstellte Tuningfile hat keine korrekten Checksummen!
                Falls das File ohne nachfolgende Korrektur der Checksummen in das Motorsteuergerät
                geschrieben wird, wird das Steuergerät das File nicht anerkennen!!!
                Das Auto wird NICHT MEHR STARTEN und das Motorsteuergerät muss wiederbelebt oder
                ausgetauscht werden!!!
            </p>
            <p>
                Das erstellte Tuningfile dient ausschliesslich Demonstrationszwecken und ist keinesfalls
                dazu bestimmt in ein Motorsteuergerät geschrieben und in Betrieb genommen zu werden!!!
            </p>
            <p>
                Dem Benutzer ist bekannt, dass der Einsatz eines erstellten Tuningfiles zum Erlöschen
                der allgemeinen Betriebserlaubnis führt. Dies hat die Konsequenz, dass das so veränderte
                Fahrzeug nicht im öffentlichen Straßenverkehr benutzt werden darf. Ebenfalls ist dem
                Benutzer bekannt, dass ohne allgemeine Betriebserlaubnis auch kein Versicherungsschutz besteht
                und sich durch das Abrufen der Mehrleistung der Verschleiß der Fahrzeugteile erhöht.
            </p>
            <p>
                Der Autor dieses Programms haftet nicht für Schäden, die beim Fahrzeugbetrieb eines durch
                ein hier erstelltes Tuningfile veränderten Fahrzeuges ohne Allgemeine Betriebserlaubnis
                und/oder ohne Versicherungsschutz entstehen.
            </p>
            <p>
                Durch das Aufspielen eines Tuningfiles erlöschen Herstellergarantie und jegliche Gewährleistungsansprüche
                in Bezug auf das veränderte Fahrzeug. Hierfür kann der Autor dieser Software nicht haftbar gemacht werden!
            </p>
            <p>
                Mit dem Erstellen eines Tuningfiles und dem Akzeptieren des Haftungsausschluss, bestätigt der Benutzer
                dieses Programms den oben genannten Verwendungszweck des erstellten Tuningfiles.
            </p>
            <button id="acceptButton" onclick="$('#patchCreatorForm').submit();$('#disclaimerDialog').dialog('close');">Ich akzeptiere den Haftungsausschluss</button> <button id="cancelButton" onclick="$('#disclaimerDialog').dialog('close');">Abbrechen</button>
        </div>
        <div id="downloadDialog" title="Tuningfile herunterladen">
            <p>Das gewählte Tuningfile wurde erfolgreich erstellt und kann nun heruntergeladen werden.</p>
            <p>
                <strong>MD5 Hash:</strong> <span id="md5"></span><br />
                <strong>Dateiname:</strong> <span id="filename"></span>
            </p>
            <p style="text-align: center;">
                <button id="downloadButton" onclick="location.href=window.filepath;">
                    <div style="float: left;margin-right: 10px;">
                        <img src="img/download.png" />
                    </div>
                    <span id="downloadLabel">
                        Download
                    </span><br />
                    <span id="downloadSub">Klicken, um das Tuningfile herunterzuladen</span>
                </button>
                <!-- <strong>Downloadlink:</strong> <span id="downloadlink"></span> -->
            </p>
        </div>
        <div id="debugDialog" title="Debug Fenster">
            <iframe id="uploadFrame" name="uploadFrame" style="width: 100%;display: block;border: none;height: 200px;<?php if(!_DEBUG): ?>display: none;<?php endif; ?>"></iframe>
        </div>
    </body>
</html>
