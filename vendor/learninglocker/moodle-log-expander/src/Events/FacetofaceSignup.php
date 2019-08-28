<?php namespace LogExpander\Events;

class FacetofaceSignup extends Event {
    /**
     * Reads data for an event.
     * @param [String => Mixed] $opts
     * @return [String => Mixed]
     * @override Event
     */
    public function read(array $opts) {
        if (!empty($opts['other'])) {
            $other = unserialize($opts['other']);
        } else {
            return array();
        }
        $session = $this->repo->readFacetofaceSession($other['sessionid']);
        return array_merge(parent::read($opts), [
            'module' => $this->repo->readModule($session->facetoface, 'facetoface'),
            'session' => $session
        ]);
    }
}