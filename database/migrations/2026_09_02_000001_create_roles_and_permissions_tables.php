<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('roles', function(Blueprint $t){$t->id();$t->string('name');$t->string('slug')->unique();$t->timestamps();});
  Schema::create('permissions', function(Blueprint $t){$t->id();$t->string('name');$t->string('slug')->unique();$t->timestamps();});
  Schema::create('permission_role', function(Blueprint $t){$t->foreignId('permission_id')->constrained()->cascadeOnDelete();$t->foreignId('role_id')->constrained()->cascadeOnDelete();$t->primary(['permission_id','role_id']);});
 }
 public function down(): void {Schema::dropIfExists('permission_role');Schema::dropIfExists('permissions');Schema::dropIfExists('roles');}
};
