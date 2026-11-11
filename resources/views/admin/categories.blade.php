@extends('layouts.dashboard', ['role' => 'admin'])
@section('title', 'Product Categories')
@section('content')
<div class="grid grid-2">
    <div class="card"><h3 class="mb-16">All Categories</h3>
        <div class="table-wrap"><table><thead><tr><th>Name</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead><tbody>
            @forelse ($categories as $category)
                <tr>
                    <td><form method="POST" action="{{ route('admin.categories.update', $category->id) }}" class="row gap-8">@csrf<input name="name" value="{{ $category->name }}" maxlength="60" required><button class="btn btn-outline btn-sm" type="submit">Save</button></form></td>
                    <td>{{ $category->products_count }}</td><td>{{ $category->is_active ? 'Active' : 'Inactive' }}</td>
                    <td><form method="POST" action="{{ route('admin.categories.status', $category->id) }}">@csrf<button class="btn btn-outline btn-sm" type="submit">{{ $category->is_active ? 'Deactivate' : 'Activate' }}</button></form></td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No categories yet.</td></tr>
            @endforelse
        </tbody></table></div>
    </div>
    <form class="card" method="POST" action="{{ route('admin.categories.store') }}" style="align-self:start">
        @csrf
        <h3 class="mb-16">Add Category</h3>
        <div class="form-group"><label for="category-name">Category Name</label><input id="category-name" name="name" required maxlength="60" value="{{ old('name') }}" placeholder="e.g. Spices"></div>
        <button class="btn btn-block" type="submit">Add Category</button>
    </form>
</div>
@endsection
