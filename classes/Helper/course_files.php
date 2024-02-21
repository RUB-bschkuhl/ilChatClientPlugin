<?php

class course_files
{

    /**
     * course_files constructor.
     * @param integer $courseid
     * @param \context $context
     * @throws \moodle_exception
     */
    public function __construct($courseid, \context $context)
    {
        $this->courseid = $courseid;
        $this->context = $context;
    }

    /**
     * @var \context
     */
    protected $context;

    /**
     * @var int
     */
    protected $filescount = -1;

    /**
     * @var array
     */
    protected $filelist = null;

    /**
     * @var int
     */
    protected $courseid;


    /**
     * @return array
     */
    public function get_filelist()
    {
        if ($this->filelist == null) {
            $this->filelist = $this->get_files();
        }
        return $this->filelist;
    }

    /**
     * @return int
     */
    public function get_filescount()
    {
        if ($this->filescount == -1) {
            $this->filescount = count($this->get_filelist());
        }
        return $this->filescount;
    }

    /**
     * @return array
     */
    protected function get_files()
    {
        global $DB;
        $cid = $this->courseid;
        // gets course union course modules union course blocks
        $sql_resources = 'SELECT f.* FROM mdl_files f 
        JOIN mdl_context ctx 
        JOIN mdl_course_modules cm 
        JOIN mdl_course c ON f.contextid=ctx.id  AND ctx.instanceid=cm.id  AND cm.course=' . $cid . ' Where component="mod_resource" AND f.userid IS NOT null AND f.filesize>0
        UNION 
        SELECT f.* FROM mdl_files f 
        JOIN mdl_context ctx 
        JOIN mdl_course_modules cm 
        JOIN mdl_course c ON f.contextid=ctx.id  AND ctx.instanceid=cm.id  AND cm.course=' . $cid . ' Where component="mod_folder"  AND f.filesize>0;';

        $this->filelist = $DB->get_records_sql($sql_resources);

        return $this->filelist;
    }
}
