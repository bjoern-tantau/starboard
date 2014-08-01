<?php

class BaseController extends Controller {

    /* @var Illuminate\View\View */
    public $layout = 'layouts.main';

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout, array(
                'globalErrors' => true,
                'message' => Session::get('message'),
            ));
		}
	}

}
