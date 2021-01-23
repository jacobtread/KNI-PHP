<?php

namespace Jacobtread\KNI;

class Notice {

    public string $level;
    public string $subject;
    public string $body;
    public string $teacher;

    /**
     * Notice constructor.
     * @param string $level The level of user this notice is targeted to
     * @param string $subject The subject/title content of the notice
     * @param string $body The body/content of the notice
     * @param string $teacher The teacher that posted the notice
     */
    public function __construct(string $level, string $subject, string $body, string $teacher) {
        $this->level = $level;
        $this->subject = $subject;
        $this->body = $body;
        $this->teacher = $teacher;
    }

    /**
     * @return bool
     */
    public function isMeeting(): bool {
        return false;
    }

    /**
     * @return string
     */
    public function getLevel(): string {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getSubject(): string {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getBody(): string {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getTeacher(): string {
        return $this->teacher;
    }

}