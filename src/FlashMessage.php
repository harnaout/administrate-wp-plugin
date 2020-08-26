<?php
namespace ADM\WPPlugin;

if (!class_exists('FlashMessage')) {

    /**
     * This class will be responsible for displaying the messages on the front end.
     *
     * @package default
     *
     */
    class FlashMessage
    {

        protected static $instance;
        public $message;
        public $success;

        /**
         * Initializes plugin variables and sets up WordPress hooks/actions
         *
         * @return void
         *
         */
        protected function __construct()
        {
            $this->readMessage();
        }

        /**
         * Static Singleton Factory Method
         * Return an instance of the current class if it exists
         * Construct a new one otherwise
         *
         * @return FlashMessage object
         *
         */
        public static function instance()
        {
            if (!isset(self::$instance)) {
                $className = __CLASS__;
                self::$instance = new $className;
            }
            return self::$instance;
        }

        public function setMessage($options = array())
        {
            $this->message  = @$options['message'] ?: '';
            $this->success  = @$options['success'] ?: 'success';

            if (!session_id()) {
                session_start();
            }

            $_SESSION["flash"] = serialize($this);
        }

        public function clearMessage()
        {
            $this->message  = null;
            $this->success  = null;

            if (!session_id()) {
                session_start();
            }

            unset($_SESSION["flash"]);
        }

        public function readMessage()
        {
            if (!session_id()) {
                session_start();
            }

            if (!isset($_SESSION["flash"])) {
                return;
            }

            $flash = unserialize(@$_SESSION["flash"]);

            if (! empty($flash)) {
                $this->message  = $flash->message;
                $this->success  = $flash->success;
            }
        }

        public function displayMessage()
        {
            $this->readMessage();

            $content = '';
            $message = $this->message;
            $success = $this->success;

            if (! empty($message)) {
                $class = 'show';

                ob_start();
                include(ADMWPP_TEMPLATES_DIR . 'flash_message.php');
                $content = ob_get_contents();
                ob_end_clean();
            }

            $this->clearMessage();

            echo $content;
        }
    }
}
