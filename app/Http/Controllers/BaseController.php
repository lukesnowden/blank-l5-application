<?php namespace App\Http\Controllers;

use Session;

class BaseController extends Controller {

	/**
	 * [$layout description]
	 * @var string
	 */
	protected $layout = 'Global.base';

	/**
	 * [$header description]
	 * @var string
	 */
	protected $header = 'Global.header';

	/**
	 * [$footer description]
	 * @var string
	 */
	protected $footer = 'Global.footer';

	/**
	 * [$HTMLheader description]
	 * @var string
	 */
	protected $HTMLheader = 'Global.HTMLheader';

	/**
	 * [$HTMLfooter description]
	 * @var string
	 */
	protected $HTMLfooter = 'Global.HTMLfooter';

	/**
	 * [$data description]
	 * @var array
	 */
	protected $data = array();

	/**
	 * [$errors description]
	 * @var boolean
	 */
	protected $errors = false;

	/**
	 * [__construct description]
	 */
	public function __construct() {

	}

	/**
	 * [callAction description]
	 * @param  [type] $method     [description]
	 * @param  [type] $parameters [description]
	 * @return [type]             [description]
	 */
	public function callAction($method, $parameters)
    {
        $this->setupLayout();
        $response = call_user_func_array(array($this, $method), $parameters);
        if ( is_null($response ) && ! is_null( $this->layout ) ) {
            $response = $this->layout;
        }
        return $response;
    }

    /**
     * [setLayout description]
     * @param [type] $name [description]
     */
    protected function setLayout($name)
    {
        $this->layout = $name;
    }

	/**
	 * [setupLayout description]
	 * @return [type] [description]
	 */
	protected function setupLayout() {
		if ( ! is_null( $this->layout ) ) {
			$this->layout = view( $this->layout, $this->data );
		}
		$this->layout->nest( 'HTMLheader', $this->HTMLheader, array( 'header' => view( $this->header ) ) );
		$this->layout->nest( 'HTMLfooter', $this->HTMLfooter, array( 'footer' => view( $this->footer ) ) );
		if( is_null( $this->layout->content ) ) {
			$this->layout->content = '';
		}
	}

	/**
	 * [backToPreviousRequest description]
	 * @param  array  $errors [description]
	 * @return [type]         [description]
	 */
	public function backWithInputAndErrors( $errors = array() ) {
		return redirect()->back()->withInput()->withErrors( $errors );
	}

	/**
	 * [back description]
	 * @return [type] [description]
	 */
	public function back() {
		return redirect()->back();
	}

	/**
	 * [success description]
	 * @param  [type] $message [description]
	 * @return [type]          [description]
	 */
	public function success( $message ) {
		Session::flash( 'success', $message );
	}

	/**
	 * [redirectTo description]
	 * @param  [type] $to [description]
	 * @return [type]     [description]
	 */
	public function redirectTo( $to )
	{
		return redirect( $to );
	}

}
