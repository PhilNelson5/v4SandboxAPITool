  <button id="myBtn">Redirect</button>
  <script>
    var btn = document.getElementById('myBtn');
    btn.addEventListener('click', function() {
      document.location.href = 'index2.html';
    });
  </script>
<?php

echo ' <a href="index2.html">page1</a>';
echo '<a href="dataCollector.php">page2</a>';

debug_to_console("DataCollector page:", $_GET);
if(isset($_POST['function'])) {
    if($_POST['function'] == 'getSessionTags') {
        echo("<script>console.log('postttttttttttttt Do GetSessionTags Work');</script>");
        getSessionTags($_POST);
    } elseif($_POST['function'] == 'anotherFunction') {
        echo("<script>console.log('function=anotherFunction');</script>");
    }
} 

function getSessionTags($_DATA) {
    $url = "https://vsafesandbox.ecustomersupport.com/GatewayV4Proxy/Service/GetSessionTags";
  // $url = "https://tduvvsafepweb-b:8080/GatewayV4Proxy/Service/GetSessionTags";
debug_to_console('Is FingerPrint HERE:', $_DATA);
    $payload = array(
        'TransactionID' => $_DATA['TransactionID'],
        'AccountName' => $_DATA['AccountName'],
        'Password' => $_DATA['Password']
    );
    
    debug_to_console('Parameters Passed to getSessionTags:', $payload);

    $context = stream_context_create(array(
        'http' => array(
            'method' => 'POST',
            'header' => "Connection: close\r\n"
            . "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($payload)
        )
    ));

    $result = array();

    parse_str(file_get_contents($url, true, $context), $result);

    $error = null;
    if ($result['ResponseCode'] == 0) {
        debug_to_console('Successfully called GetSessionTags ', $result);
        $fingerprintEndpoint = 'https://fingerprint.ecustomerpayments.com/ThreatMetrixUIRedirector';
        $embedHtml = sprintf('<p style="background:url(%1$s/fp/clear.png?org_id=%2$s&session_id=%3$s&m=1);"></p> <img src="%1$s/fp/clear.png?org_id=%2$s&session_id=%3$s&m=2" /> <script type="text/javascript" src="%1$s/fp/check.js?org_id=%2$s&session_id=%3$s"></script> <object data="%1$s/fp/fp.swf?org_id=%2$s&session_id=%3$s" type="application/x-shockwave-flash" width="1" height="1"> <param value="%1$s/fp/fp.swf?org_id=%2$s&session_id=%3$s" name="movie" /> </object>'
                , $fingerprintEndpoint
                , $result['OrgID']
                , $result['WebSessionID']);
//debug_to_console('FINGERPRINT EMBEDDED STRING:', $embedHtml);
        echo $embedHtml;
        
        $dataCollector = sprintf('<script src="https://collectorsvc.ecustomersupport.com/DCCSProxy/Service/vdccs.js?AccountName=%d&WebSessionID=%d"></script>'
                , $result['OrgID']
                , $result['WebsessionID']);
        debug_to_console('DataCollector string to embed in page:', $dataCollector);
    } else {
        // An error occurred
        $error = $result['ResponseText'];
        debug_to_console('An Error Happened: ', $error);
    }
    
    return $result;
}

function debug_to_console($message, $data) {
    if (is_array($data) || is_object($data)) {
        echo("<script>console.log('PHP: $message " . json_encode($data) . "');</script>");
    } else {
        echo("<script>console.log('PHP: $message" . $data . "');</script>");
    }
}