<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the 'roles' table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Unique name for each role
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {

           // Add the 'last_name' field after 'name'
            $table->string('last_name')->after('name');

            // Change the position of the fields as required
            $table->string('auth_provider')->nullable()->after('password');

             // Add the 'role_id' field as a foreign key
             $table->unsignedBigInteger('role_id')->after('password')->nullable();

             // Configure the relationship with the 'roles' table
             $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');

        });

        // Create the 'user_sessions' table
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Relationship with the 'users' table
            $table->string('provider_id'); // Authentication provider ID
            $table->string('session_token')->unique(); // Unique token for the session
            $table->timestamp('expires_at');
            $table->timestamps();

            // Relationship with the 'users' table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete the 'user_sessions' table
        Schema::dropIfExists('user_sessions');

        // Delete the 'roles' table
        Schema::dropIfExists('roles');

        // Revert the changes in the 'users' table
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
            $table->dropColumn('last_name');
        });
    }
};
