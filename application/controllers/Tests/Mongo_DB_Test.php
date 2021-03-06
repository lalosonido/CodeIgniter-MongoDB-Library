<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mongo DB Test suite
 * Use the index method of Mongo_DB_Test controller to run all the test cases with the exception of 
 * test_delete_document_by_id() and test_delete_documents() which should be called individually.
 * 
 * @package CodeIgniter
 * @author Bayonle ladipo<laredy27@gmail.com>
 * @version version 1.0
 */

class Mongo_DB_Test extends CI_Controller {
    const COLLECTION = "test_collection";
    public function __construct() {
        parent::__construct();
        $this->load->library("unit_test");
        $this->load->library("mongo_db", array("activate" => "test"), "db");
        $this->load->helper("common");
    }
    
    public function index(){
       $this->connect();
       $this->test_insert_document();
       $this->test_another_insert_document();
       $this->test_insert_many_document();
       $this->test_find_documents();
       $this->test_find_documents_where();
       $this->test_get_one_document();
       $this->test_count_results();
       $this->test_count_all();
       $this->test_get_distinct();
       $this->test_update_document();
       $this->test_update_all_documents();
       echo $this->unit->report();
    }
    
    public function connect(){
        $test = $this->db;
        $this->unit->run($test, "is_object", "Conect to mongo db and select table");
    }
    
    /**
     * Single insert test case
     * 
     * $expcectedResult BSON ObjectId
     */
    public function test_insert_document(){
        $data = array("title" => "learn codeigniters");
        $test = $this->db->insert($this::COLLECTION, $data);
        $this->unit->run($test, "is_object", "Insert a single mongodb document");
    }
    
    /**
     * Single insert with explicit _id attribute test case
     * 
     * $expcectedResult _id String
     */
    public function test_another_insert_document(){
        $data = array("_id" => "1994", "title" => "Another Insert");
        $test = $this->db->insert($this::COLLECTION, $data);
        $this->unit->run($test, "is_string", "Insert a single mongodb document with explicit _id");
    }
    /**
     * Batch insert test case
     * 
     * $expcectedResult Array of inserted documents ids
     */
    public function test_insert_many_document(){
        $data = array(
            array("_id" => "1996", "title" => "learn codeigniters"),
            array("title" => "do something else")
                  
        );
        $test = $this->db->batch_insert($this::COLLECTION, $data);
        $this->unit->run($test, "is_array", "Insert multiple mongodb documents");
    }
    
    /**
     * Generic Find test case
     * 
     * $expcectedResult Array of result data
     */
    public function test_find_documents(){
        $test = $this->db->get( $this::COLLECTION );
        $this->unit->run($test, "is_array", "Find all the doucuments in the collection and return them as an array.");
    }
    
    /**
     * Find Where test case
     * 
     * $expcectedResult Array of selected data
     */
    public function test_find_documents_where(){
        $test = $this->db->where("title", "learn codeigniters")->get( $this::COLLECTION );
        $this->unit->run($test, "is_array", "Find all the doucuments in the collection that match the where query and return them as an array.");
    }
    
    /**
     * Find one document test
     * 
     * $expectedResult: array
     */
    public function test_get_one_document(){
        $test = $this->db->find_one( $this::COLLECTION );
        $this->unit->run($test, "is_array", "Find and return only one document as an array");
    }
    
    /**
     * Count all documents in the result  
     * 
     * $expectedResult: int
     */
    public function test_count_results(){
        $test = $this->db->where("title", "learn codeigniters")->count( $this::COLLECTION );
        $this->unit->run( $test, 4, "Return the number of documents in a collection" );
    }
    
    /**
     * Count all documents in collection test  
     * 
     * $expectedResult: int
     */
    public function test_count_all(){
        $test = $this->db->count_all( $this::COLLECTION );
        $this->unit->run( $test, "is_int", "Return the number of documents in a collection" );
    }
    
    /**
     * Get distinct values test case
     * 
     * $expectedResult: array
     */
    public function test_get_distinct(){
        $test = $this->db->distinct( $this::COLLECTION, "title" );
        $this->unit->run( $test, "is_array", "Return an array of distinct values" );
    }
    
    /**
     * Update a single document test case
     * 
     * $expectedResult: boolean
     */
    function test_update_document(){
        $test1 = $this->db->where("_id", "1994")->set("new_field", "This field doesn't exist but has been added automatically by 'set'")->update( $this::COLLECTION );
        $this->unit->run( $test1, TRUE, "Update a single document specified" );
        $test2= $this->db->where("_id", "1994")->set("array_field", array())->update( $this::COLLECTION );
        $test3 = $this->db->where("_id", "1994")->addToSet("array_field", array("foo", "bar"))->update( $this::COLLECTION );
        $this->unit->run( $test3, TRUE, "Append a value to an array field in a single document and update" );
        $test4 = $this->db->where("_id", "1994")->pop("array_field")->update( $this::COLLECTION );
        $this->unit->run( $test4, TRUE, "Remove the last element from an array field in a document and update the document" );
    }
    
    /**
     * Update all documents test case
     * 
     * $expectedResult: boolean
     */
    function test_update_all_documents(){
        $docs = $this->db->where("title", "learn codeigniter");
        $test = $docs->update_all( $this::COLLECTION, array('$set' => array( "title" => "foo")));
        $this->unit->run( $test, TRUE, "Update all documents matched" );
    }
    
    /**
     * Delete a single document using the document's id test case
     * 
     * $expectedResult: boolean
     */
    function test_delete_document_by_id($_id="", $mongo_id=TRUE){
        if( empty($_id) ){
            exit; // Stop test execution
        }
        $_id = ($mongo_id === TRUE) ? new MongoDB\BSON\ObjectId($_id) : $_id;
        $doc = $this->db->where( "_id", $_id );
        $test = $doc->delete( $this::COLLECTION );
        echo $this->unit->run( $test, TRUE, "Delete the document matching the id specified" );
    }
    
    /**
     * Delete all documents test case
     * 
     * $expectedResult: boolean
     */
    function test_delete_documents(){
        $doc = $this->db->where( array("title" => "foo") );
        $test = $doc->delete_all( $this::COLLECTION );
        echo $this->unit->run( $test, TRUE, "Delete all the documents from a collection matching the filter" );
    }
    
    
    function test_mongo_date(){
        $test = $this->db->date();
        print_arr($test->toDateTime()->format('r'));
    }
    
}
