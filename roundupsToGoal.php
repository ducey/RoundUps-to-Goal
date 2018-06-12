<?php
// Starling Personal Access Token
$starlingPAT = "";


// RoundUps Savings Goal UID
$savingsGoalUID = "";


// Email Address
$emailAddress = "";


// Connect to Starling
$authorization = "Authorization: Bearer ". $starlingPAT;


$d = new DateTime('first day of this month'); 
$today = new DateTime('today');

$month_start = new DateTime("yesterday");
$month_end = new DateTime("today");

// Get transactions
$process = curl_init("https://api.starlingbank.com/api/v1/transactions?from=" . $month_start->format('Y-m-d') . "&to=" . $month_end->format('Y-m-d'));
curl_setopt($process, CURLOPT_HTTPHEADER, array(
    $authorization)                                                           
);
curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
$starling = curl_exec($process);
curl_close($process);
$starlingTransResp = json_decode($starling);
$starlingTrans = $starlingTransResp->{'_embedded'}->{'transactions'};
$starlingTransOut = "";
$starlingTotalRoundUps = 0;
$i = 1;
foreach($starlingTrans as $tran) {

    $amnt = abs(number_format((float)$tran->{'amount'}, 2, '.', ''));

    $diff = ceil($amnt) - $amnt;
    $starlingTotalRoundUps += $diff;

}

$output = array(
    "round_ups" => number_format((float)$starlingTotalRoundUps, 2, '.', '')
);

// echo json_encode($output);

 
// savings-goals
$putUrl = "https://api.starlingbank.com/api/v1/savings-goals/". $savingsGoalUID. 
"/add-money/". 
unique_id(8). "-". 
unique_id(4). "-". 
unique_id(4). "-". 
unique_id(4). "-". 
unique_id(12);

$process = curl_init($putUrl);
$data = array('currency' => 'GBP','minorUnits' => $starlingTotalRoundUps * 100);
$payload = json_encode( array ('amount' => $data)  ); 


curl_setopt($process, CURLOPT_HEADER, true);
// curl_setopt($process, CURLINFO_HEADER_OUT, true);
curl_setopt($process, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($process, CURLOPT_POSTFIELDS, $payload);
curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
curl_setopt($process, CURLOPT_HTTPHEADER,
               array('Content-Type: application/json',
                     'Accept: application/json',
                     'Expect: 100-continue', 
                      'Content-Length: ' . strlen($payload),
                      $authorization)
);
$savingsGoals = curl_exec($process);

curl_close($process);
$savingsGoalsResp = json_decode($savingsGoals);


// echo json_encode($savingsGoalsResp);



mail($emailAddress, "RoundUps","Today you've saved £". number_format((float)$starlingTotalRoundUps, 2, '.', ''). 
" in RoundUps!");


function unique_id($l = 8) {
    return substr(md5(uniqid(mt_rand(), true)), 0, $l);
}

?>







