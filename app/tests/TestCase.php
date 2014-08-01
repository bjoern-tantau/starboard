<?php

class TestCase extends Illuminate\Foundation\Testing\TestCase
{

    /**
     * Default preparation for each test
     */
    public function setUp()
    {
        parent::setUp();

        $this->prepareForTests();
    }

    /**
     * Migrate the databse and set the mailer to 'pretend'.
     */
    protected function prepareForTests()
    {
        Artisan::call('migrate');
        Mail::pretend(true);
    }

    protected function resetEvents()
    {
        // Get all models in the Model directory
        $pathToModels = '/app/models';   // <- Change this to your model directory
        $files = File::files($pathToModels);

        // Remove the directory name and the .php from the filename
        $files = str_replace($pathToModels . '/', '', $files);
        $files = str_replace('.php', '', $files);

        // Remove "BaseModel" as we dont want to boot that moodel
        if (($key = array_search('BaseModel', $files)) !== false) {
            unset($files[$key]);
        }

        // Reset each model event listeners.
        foreach ($files as $model) {

            // Flush any existing listeners.
            call_user_func(array($model, 'flushEventListeners'));

            // Reregister them.
            call_user_func(array($model, 'boot'));
        }
    }

    /**
     * Creates the application.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $unitTesting = true;

        $testEnvironment = 'testing';

        return require __DIR__ . '/../../bootstrap/start.php';
    }

}
