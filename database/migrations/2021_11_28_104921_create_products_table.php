<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string("name",25);
            $table->string('image')->nullable();
            $table->unsignedInteger("seller_id");
            $table->date("expiretion_date");
            $table->enum("category",["vegetable","fruits","canned"]);
            $table->string("phone_no");//it same that in seller profile page
            $table->integer("quantity");
            $table->float("price");
            $table->float("discount_1");
            $table->integer("discount_period_1");
            $table->float("discount_2");
            $table->integer("discount_period_2");
            $table->float("discount_3");
            $table->integer("discount_period_3");
            $table->integer("views")->default(0);
            $table->integer("likes_counts")->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
