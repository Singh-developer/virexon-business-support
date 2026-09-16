<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * Shared trash behaviour for models that support soft deletes.
 *
 *  - Soft deleting a parent soft deletes its related records (cascade trash).
 *  - Restoring a parent restores its related records (cascade restore).
 *  - Uploaded files are kept while a record sits in the trash and are only
 *    removed from disk when the record is permanently deleted.
 */
trait HandlesTrash
{
    protected static function bootHandlesTrash(): void
    {
        static::deleting(function ($model) {
            if ($model->isForceDeleting()) {
                $model->deleteTrashFiles();
                $model->cascadeForceDelete();
            } else {
                $model->cascadeDelete();
            }
        });

        static::restoring(function ($model) {
            $model->cascadeRestore();
        });
    }

    /**
     * Relation names that should be trashed together with this model.
     *
     * @return array<int, string>
     */
    protected function trashRelations(): array
    {
        return [];
    }

    /**
     * Additional relation names that only exist for permanent cleanup.
     *
     * @return array<int, string>
     */
    protected function forceTrashRelations(): array
    {
        return [];
    }

    /**
     * Map of disk => file attribute(s) to remove on permanent delete.
     *
     * @return array<string, string|array<int, string>>
     */
    protected function trashFileMap(): array
    {
        return [];
    }

    /** Soft delete every related record, firing each model's own events. */
    public function cascadeDelete(): void
    {
        foreach ($this->trashRelations() as $relation) {
            if (! method_exists($this, $relation)) {
                continue;
            }

            foreach ($this->{$relation}()->get() as $child) {
                if (method_exists($child, 'delete')) {
                    $child->delete();
                }
            }
        }
    }

    /** Permanently delete every related record, firing each model's events. */
    public function cascadeForceDelete(): void
    {
        $relations = array_unique(array_merge($this->trashRelations(), $this->forceTrashRelations()));

        foreach ($relations as $relation) {
            if (! method_exists($this, $relation)) {
                continue;
            }

            foreach ($this->{$relation}()->withTrashed()->get() as $child) {
                if (method_exists($child, 'forceDelete')) {
                    $child->forceDelete();
                }
            }
        }
    }

    /** Restore every related record that is currently trashed. */
    public function cascadeRestore(): void
    {
        foreach ($this->trashRelations() as $relation) {
            if (! method_exists($this, $relation)) {
                continue;
            }

            foreach ($this->{$relation}()->onlyTrashed()->get() as $child) {
                if (method_exists($child, 'restore')) {
                    $child->restore();
                }
            }
        }
    }

    /** Remove uploaded files from disk. Only called on permanent delete. */
    protected function deleteTrashFiles(): void
    {
        foreach ($this->trashFileMap() as $disk => $attributes) {
            foreach ((array) $attributes as $attribute) {
                $path = $this->{$attribute} ?? null;

                if (! empty($path)) {
                    Storage::disk($disk)->delete($path);
                }
            }
        }
    }
}
