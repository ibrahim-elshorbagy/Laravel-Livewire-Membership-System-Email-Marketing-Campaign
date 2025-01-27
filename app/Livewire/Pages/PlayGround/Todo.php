<?php

namespace App\Livewire\Pages\PlayGround;

use App\Models\PlayGround\Todo as PlayGroundTodo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Illuminate\Support\Facades\Validator;

class Todo extends Component
{
    use LivewireAlert;
    use WithPagination;

// --------------------------------------------------------------------------------------------------------------------

    public $title;
    public $description;

    protected $listeners = [
        'Deleted'
    ];


    #[Rule('nullable|string')]
    public $search;

    public function create(){

        $data = $this->validate([
            'title' => 'required|min:3',
            'description' => 'nullable|string',
        ]);

        $data['user_id']=Auth::user()->id;

        DB::beginTransaction();

        try {

            $todo = PlayGroundTodo::create($data);
            $this->reset();
            $this->resetPage();
            DB::commit();

        $this->alert('success', 'To do Successfully!', ['position' => 'bottom-end']);

        } catch (\Exception $e) {
            DB::rollBack();

            $this->alert('error', $e->getMessage(), ['position' => 'bottom-end', 'timer' => 15000]);

        }

    }

// --------------------------------------------------------------------------------------------------------------------

    public $upd_title;
    public $upd_description;
    public $editingTodoId;
    public function update(PlayGroundTodo $todo)
    {
        // Validate Livewire public properties
        $this->validate([
            'upd_title' => 'required|min:3',
            'upd_description' => 'nullable|string',
        ]);

        // Authorization check before proceeding
        if ($todo->user_id !== Auth::id()) {
            $this->alert('error', 'Unauthorized action. This To-Do does not belong to you.', ['position' => 'bottom-end']);
            return;
        }

        DB::beginTransaction();

        try {

            // Update the To-Do record
            $todo->update([
                'title' => $this->upd_title,
                'description' => $this->upd_description,
            ]);

            DB::commit();

            // Reset the input fields after successful update
            $this->reset();

            // Success alert

            $this->alert('success', 'To do Successfully Updated!', ['position' => 'bottom-end']);

        } catch (\Exception $e) {
            DB::rollBack();

            // Error alert
            $this->alert('error', $e->getMessage(), ['position' => 'bottom-end', 'timer' => 15000]);

        }
    }



// --------------------------------------------------------------------------------------------------------------------

    public function delete(PlayGroundTodo $todo){

            $this->alert('question', 'are you sure?',
            [
                'icon' => 'warning',
                'showConfirmButton' => true ,
                'showCancelButton' => true,
                'onConfirmed' => 'Deleted',
                'confirmButtonColor' => '#3085d6',
                'cancelButtonColor' => '#d33',
                'data' => ['todoId' => $todo->id],
                'position' => 'center',

            ]);
    }


    public function Deleted($data)
    {
        // Validate the todoId
        $validator = Validator::make($data, [
            'todoId' => 'required|integer|exists:todos,id',
        ]);

        if ($validator->fails()) {
            $this->alert('error', 'Invalid request.', ['position' => 'bottom-end']);
            return;
        }

        DB::beginTransaction();

        try {
            // Retrieve the validated todo
            $todo = PlayGroundTodo::findOrFail($data['todoId']);

            // Authorization check
            if ($todo->user_id !== Auth::id()) {
                $this->alert('error', 'Unauthorized action. This To-Do does not belong to you.', ['position' => 'bottom-end']);
                return;
            }

            // Delete the todo
            $todo->delete();

            DB::commit();

            // Success alert
            $this->alert('success', 'To-Do successfully deleted', ['position' => 'bottom-end']);
        } catch (\Exception $e) {
            DB::rollBack();

            // Error alert
            $this->alert('error', $e->getMessage(), ['position' => 'bottom-end', 'timer' => 15000]);
            }
        }

// --------------------------------------------------------------------------------------------------------------------

    public function toggle(PlayGroundTodo $todo){
        DB::beginTransaction();

        try {

            $todo->complated = !$todo->complated;
            $todo->save();
            DB::commit();
            $this->alert('success', 'Successfully toggled!', ['position' => 'bottom-end']);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->alert('error', $e->getMessage(), ['position' => 'bottom-end', 'timer' => 15000]);

        }
    }

// --------------------------------------------------------------------------------------------------------------------


    #[Computed()]
    public function list(){
        return PlayGroundTodo::where('user_id', Auth::user()->id)->where('title', 'like', '%' . $this->search . '%')->paginate(5);
    }


// --------------------------------------------------------------------------------------------------------------------

    public function render()
    {
        return view('livewire.pages.play-ground.todo');
    }
}
