<?php

interface IResource {
	public function get($id);
	public function create($postdata);
	public function query($postdata);
	public function update($id, $postdata);
	public function delete($id, $postdata);
}