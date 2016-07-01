<?php namespace BotTelegram\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use BotTelegram\Models\Fileentry;
use BotTelegram\Requests\BotRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FileEntryController extends Controller {

    public function index()
    {
        $entries = Fileentry::all();

        return view('fileentries.index', compact('entries'));
    }

    public function add($id, $type, BotRequests $request) {

        $files = $request->file('files');
        if($files) {
            foreach ($files as $file) {
                $filename = uniqid() . "_" . $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                Storage::put(config("telegram_bot.path_files.notification") . $filename, File::get($file));
                $entry = new Fileentry();
                $entry->mime = $file->getClientMimeType();
                $entry->original_filename = $filename;
                $entry->type = $type;
                $entry->object_id = $id;
                $entry->filename = $file->getFilename() . '.' . $extension;

                $entry->save();
            }
        }
        return response()->json(['filename'=>route('getentry', [$filename, $type])]);

    }

    public function issetFiles($id, $type) {
        $files = [];
        $entries = Fileentry::where('type', $type)->where('object_id', $id)->get();
        if($entries) {
            foreach ($entries as $entry) {
                $files[] = ["url"=>route('getentry', [$entry->original_filename, $type]), "id"=>$entry->id];
            }
        }
        return response()->json($files);
    }

    public function get($filename, $type){

        $entry = Fileentry::where('original_filename', '=', $filename)->where('type', $type)->firstOrFail();
        if($entry) {
            $file = Storage::get(config("telegram_bot.path_files.notification") . $entry->original_filename);

            return response($file, 200)
                ->header('Content-Type', $entry->mime);
        }
    }
    
    public function delete($id) {
        $result = Fileentry::find($id);
        if($result) {
            Fileentry::destroy($id);
            Storage::delete(config("telegram_bot.path_files.notification") . $result->original_filename);
        }

        return response()->json($result);
    }

}
