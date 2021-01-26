<?php

namespace Jacobtread\KNI;

require 'Notice.php';
require 'MeetingNotice.php';
require 'Notices.php';

use SimpleXMLElement;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

define('KAMAR_KEY', 'vtku'); # The KAMAR access key to access the notices
define('KAMAR_USER_AGENT', 'KAMAR/ Linux/ Android/'); # The required User-Agent for KAMAR to accept our request
define('KAMAR_DATE_FORMAT', 'd/m/Y'); # The PHP date format that KAMAR uses for the notices command

class KNI {

    private string $url;
    private HttpClientInterface $client;

    /**
     * KNI constructor.
     * @param string $host The host domain of the KAMAR Portal (e.g portal.your.school.nz)
     * or provide the full url https://portal.yours.school.nz/
     * @param bool $is_https Whether or not to use HTTPS:// with your URL
     * (This is ignored if you provide a protocol)
     */
    public function __construct(string $host, bool $is_https = true) {
        $this->client = HttpClient::create();
        if (!str_starts_with($host, 'https://') && !str_starts_with($host, 'http://')) {
            $host = ($is_https ? 'https://' : 'http://') . $host;
        }
        if (!str_ends_with($host, '/')) {
            $host .= '/';
        }
        $host .= 'api/api.php';
        $this->url = $host;
    }

    /**
     * @param string|int|null $date
     * @return Notices The notices object which contains the result and an error if occurred
     */
    public function retrieve($date = null): Notices {
        if ($date == null) {
            # Date is null so use the current date with the KAMAR_DATE_FORMAT format
            $date = date(KAMAR_DATE_FORMAT);
        }
        if (is_int($date)) {
            # Date is an int so attempt to use it as a time int and format with KAMAR_DATE_FORMAT
            $date = date(KAMAR_DATE_FORMAT, $date);
        }
        $notices_object = new Notices($date);
        try {
            $response = $this->client->request('POST', $this->url, [
                'headers' => [
                    'User-Agent' => KAMAR_USER_AGENT,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                # Our request body
                'body' => [
                    'Key' => KAMAR_KEY, # The key to access KAMAR
                    'Command' => 'GetNotices', # The KAMAR command to use
                    'ShowAll' => 'YES', # I think this is ignored but we provide anyway
                    'Date' => $date # The date of the notices we want
                ]
            ]);
            # Get the response content
            $content = $response->getContent();
            # Parse the XML response
            $xml = simplexml_load_string($content);
            # Check if the request encountered a KAMAR error
            if (isset($xml['Error'])) {
                # Get the error message
                $error = $xml['Error'];
                # Set the error message
                $notices_object->setErrorMessage($error);
            } else {
                $elements = [];
                # Check if the meeting notices element exists
                if (isset($xml->MeetingNotices)) {
                    /** @var SimpleXMLElement $meeting_notices */
                    $meeting_notices = $xml->MeetingNotices;
                    # Check if there are any meeting notice elements
                    if (isset($xml->MeetingNotices->Meeting)) {
                        /** @var SimpleXMLElement $data */
                        foreach ($xml->MeetingNotices->Meeting as $data) {
                            array_push($elements, $data);
                        }
                    }
                }

                # Check if the general notices element exists
                if (isset($xml->GeneralNotices)) {
                    # Check if there are any general notice elements
                    if (isset($xml->GeneralNotices->General)) {
                        foreach ($xml->GeneralNotices->General as $data) {
                            array_push($elements, $data);
                        }
                    }
                }
                # An array of notices
                $notices = [];
                # Loop through all the elements
                /** @var SimpleXMLElement $element */
                foreach ($elements as $element) {
                    $is_meeting = $element->getName() === 'Meeting';
                    # An array of required fields for a notice
                    $required = ['Level', 'Subject', 'Body', 'Teacher'];
                    # If the notice is a meeting add the required meeting fields
                    if ($is_meeting) {
                        array_push($required, 'PlaceMeet', 'DateMeet', 'TimeMeet');
                    }
                    # Whether or not all the required fields where found
                    $is_valid = true;
                    foreach ($required as $name) {
                        # Check if a field does not exist
                        if (!isset($element->{$name})) {
                            # Break the loop and set to invalid
                            $is_valid = false;
                            break;
                        }
                    }
                    # If invalid skip and continue with next element
                    if (!$is_valid) {
                        continue;
                    }
                    $level = (string)$element->Level;
                    $subject = (string)$element->Subject;
                    $body = (string)$element->Body;
                    $teacher = (string)$element->Teacher;
                    if ($is_meeting) {
                        $place = (string)$element->PlaceMeet;
                        $date_meet = (string)$element->DateMeet;
                        $time = (string)$element->TimeMeet;
                        array_push($notices, new MeetingNotice($level, $subject, $body, $teacher, $place, $date_meet, $time));
                    } else {
                        array_push($notices, new Notice($level, $subject, $body, $teacher));
                    }
                }
                # Set the notices in the notices object
                $notices_object->setNotices($notices);
            }
        } catch (TransportExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface | ClientExceptionInterface $e) {
            # If the request failed set the appropriate error
            $notices_object->setErrorMessage($e->getMessage());
            $notices_object->setErrorCause($e);
        }
        # Return the result
        return $notices_object;
    }

}