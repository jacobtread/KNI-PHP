<?php

namespace Jacobtread\KNI;

class MeetingNotice extends Notice {

    public string $place;
    public string $date;
    public string $time;

    /**
     * MeetingNotice constructor.
     * @param string $level The level of user this notice is targeted to
     * @param string $subject The subject/title content of the notice
     * @param string $body The body/content of the notice
     * @param string $teacher The teacher that posted the notice
     * @param string $place The place where this notice will occur
     * @param string $date The date this notice is for
     * @param string $time The time this notice is for (can be blank)
     */
    public function __construct(string $level, string $subject, string $body, string $teacher, string $place, string $date, string $time) {
        parent::__construct($level, $subject, $body, $teacher);
        $this->place = $place;
        $this->date = $date;
        $this->time = $time;
    }

    /**
     * @return bool
     */
    public function isMeeting(): bool {
        return true;
    }

    /**
     * @return string
     */
    public function getPlace(): string {
        return $this->place;
    }

    /**
     * @return string
     */
    public function getDate(): string {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getTime(): string {
        return $this->time;
    }

}