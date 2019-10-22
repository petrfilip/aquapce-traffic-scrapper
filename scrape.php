<?php
require_once __DIR__ . '/vendor/autoload.php';

$request = doRequest();
exitIfRequestFailed($request);
$dom = getDom($request);

$now = now();
$pool = getNodeValue($dom, "/html/body/div[1]/div[1]/div[1]/ul/li[1]/span");
$aqua = getNodeValue($dom, "/html/body/div[1]/div[1]/div[1]/ul/li[2]/span");
$wellness = getNodeValue($dom, "/html/body/div[1]/div[1]/div[1]/ul/li[3]/span");

echo $now . ' | '.'Bazén:' . $pool . ' | ' . 'Aqua:' . $aqua . ' | ' . 'Wellness:' . $wellness;
writeToCsv(array($now, $pool, $aqua, $wellness));

/*********************************************************/
/*********************************************************/
/*********************************************************/

function getNodeValue($dom, $xpath) {
    return getNode($dom, $xpath)->nodeValue;
}

function getNode($dom, $xpath)
{
    $nodes = getNodes($dom, $xpath);
    if ($nodes->length === 0) {
        throw new \RuntimeException("No matching node found");
    }
    return $nodes[0];
}

function getNodes($dom, $xpath)
{
    $DomXpath = new DOMXPath($dom);
    return $DomXpath->query($xpath, null);
}

function writeToCsv($array) {
    $file = new SplFileObject('stats.csv', 'a');
    $file->fputcsv($array);
    $file = null;
}

function doRequest()
{
    $headers = array('User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36');
    return Requests::get('http://www.aquapce.cz/', $headers, null);
}

function getDom(Requests_Response $request): DOMDocument
{
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($request->body);
    libxml_clear_errors();
    return $dom;
}

function exitIfRequestFailed(Requests_Response $request): void
{
    if ($request->status_code != 200) {
        exit;
    }
}

function now()
{
    $now = new DateTime();
    $now->setTimezone(new DateTimeZone('Europe/Prague'));
    return $now->format('Y-m-d H:i:s');
}

