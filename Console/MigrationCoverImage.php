<?php

namespace Gdevilbat\SpardaCMS\Modules\Post\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Gdevilbat\SpardaCMS\Modules\Post\Entities\PostMeta;
use Gdevilbat\SpardaCMS\Modules\Post\Entities\Post;

class MigrationCoverImage extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'spardacms:post-migrate-cover-image';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migration Feature Image To Cover Image Object';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $existing_cover_image =  PostMeta::where('meta_key', 'cover_image')->pluck(Post::FOREIGN_KEY);

        $count_progress  = PostMeta::where('meta_key', 'feature_image')->whereNotIn(Post::FOREIGN_KEY, $existing_cover_image)->count();

        $feature_images = PostMeta::where('meta_key', 'feature_image')->get();

        $bar = $this->output->createProgressBar($count_progress);

        $bar->start();


        foreach ($feature_images as $key => $feature_image) 
        {
            $cover_image = PostMeta::where('meta_key', 'cover_image')->where(Post::FOREIGN_KEY, $feature_image[Post::FOREIGN_KEY])->first();

            if(empty($cover_image))
            {
                $new_cover_image = new PostMeta;
                $new_cover_image[Post::FOREIGN_KEY] = $feature_image[Post::FOREIGN_KEY];
                $new_cover_image->meta_key = 'cover_image';
                $new_cover_image->meta_value = ['file' => $feature_image->meta_value, 'caption' => ''];
                $new_cover_image->save();
                $bar->advance();
            }

        }

        $this->info("Cover Image Has Been Migrated");

        return 0;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
