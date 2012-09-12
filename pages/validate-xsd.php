<?php
/* $error_msg and $upload_msg are both set as global variables in:
 *  functions/fetch-data_from_urls.php
 *  functions/process_files.php
 * 
 * If the file upload is problematic, we issue and alert and present the upload form again
 * If successful we present a tabbed interface with info about the file.
*/
?>
<?php if(isset($error_msg)) :?>
		<div class="alert alert-error">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong>Sorry.</strong> We can't test that file.<br/>
			<?php echo $error_msg; ?>
		</div>
<?php endif; ?>	
	

<?php if( !isset($_SESSION['uploadedfilepath']) ) :?>
	 <?php header('Location: index.php'); ?>
<?php else: ?>
		<?php
			$file_path = $_SESSION['uploadedfilepath']; //Sanitise/Check this?
			libxml_use_internal_errors(true);
			
			$xml = new DOMDocument();
			$xml->load($file_path);
			  
			if ($xml->getElementsByTagName("iati-organisation")->length == 0) {
			//$xsd = "http://iatistandard.org/downloads/iati-activities-schema.xsd";
			$xsd = "iati-schema/iati-activities-schema.xsd";
			$schema = "Activity";
			
			//if ($myinputs['org'] == "1") { //sanitized $_GET['orgs']
			//  continue;
			//}
			} else {
			$xsd = "http://iatistandard.org/downloads/iati-organisations-schema.xsd";
			$schema = "Organisation";
			}
			
			if ($xml->schemaValidate($xsd)) {
				$valid = TRUE;
			} else {
				$valid = FALSE;
				//libxml_display_all_errors();
			}
			
		?>
		<h2>Validation against the IATI <?php echo $schema; ?> Schema</h2>
		<ul class="nav nav-tabs" id="myTab">
		  <li class="active"><a href="#status">Status</a></li>
		  <?php if ($valid == FALSE): ?>
			<li><a href="#extra">Extra info</a></li>
		  <?php endif; ?>
		  <!--<li><a href="#settings">Settings</a></li>-->
		</ul>
		 
		<div class="tab-content">
		  <div class="tab-pane active" id="status">
			  
				<!--<div class="row">
					<div class="span9">
						<div class="span5">-->
							<?php if ($valid == TRUE): ?>
								<h3 class="success">Success</h3>
								<div>This file validates against the IATI <?php echo $schema; ?> Schema<br/><br/></div>
							<?php else: ?>
								<h3 class="fail">Fail</h3>
								<div id="intext">
									This file does NOT validate against the IATI <?php echo $schema; ?> Schema<br/><br/>
									There are <?php echo count(libxml_get_errors()); ?> errors.<br/><br/>
									See <a href="#extra">Extra info</a> for details.
								</div>
							<?php endif; ?>
						<!--</div>
					</div>
				</div>-->
		    </div>
		  <?php if ($valid == FALSE): ?>
			<div class="tab-pane" id="extra">
				<?php libxml_display_all_errors(); ?>
			</div>
		  <?php endif; ?>
		  <!--<div class="tab-pane" id="settings">4</div>-->
		</div>
 
<?php endif; ?>
<?php

function libxml_display_all_errors() {
    $errors = libxml_get_errors();
    $codes = array();
    print("<table id='errors' class='table-striped'><thead><th>Line</th><th>Severity and code</th><th>Message</th></thead><tbody>");
    $i=1;
    if ($i % 2 == 0) {
		$class = 'even';
	} else {
		$class ='odd';
	}
    foreach ($errors as $error) {
		$code = $error->code; 
		//if (!in_array($code,$codes)) {
			$codes[] = $code;
			if ($i % 2 == 0) {
				$class = 'even';
			} else {
				$class ='odd';
			}
			$i++;
			print libxml_display_error($error,$class);
		//}
    }
    print("</tbody></table>");
    libxml_clear_errors();
}

function libxml_display_error($error,$class) {
	//print_r($error);
    $return = '<tr>';
     $return .= "<td>$error->line</td>";
    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "<td class='warning'><b>Warning $error->code</b></td>";
            break;
        case LIBXML_ERR_ERROR:
            $return .= "<td class='error'><b>Error $error->code</b></td>";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "<td class='fatal'><b>Fatal Error $error->code</b></td>";
            break;
    }
    $return .= "<td>" . trim($error->message) . "</td>";
    //if ($error->file) {
       // $return .=    " in <b>" . basename($error->file) . "</b>";
    //}
    $return .= "</tr>";

    return $return;
}
?>
