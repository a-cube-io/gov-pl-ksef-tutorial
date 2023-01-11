<?php

namespace Src\Repository\Interfaces;

interface InvoiceRepositoryInterface
{
	// returns list of all files
	public function index();

	// create new file record
	public function store(array $data);

	// change status for specified file
	public function update($id, $status);

	// fetch file record
	public function find($id);

	// delete file record
	public function delete($id);
}
