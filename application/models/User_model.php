<?php
class User_model extends CI_Model
{
    public function get_data($name)
    {
        $this->db->where('username', $name);
        return $this->db->get('tb_user')->row();
    }

    public function count_user($level)
    {
        return $this->db->get_where('tb_user', ['level' => $level])->num_rows();
    }

    public function get_user($level)
    {
        return $this->db->get_where('tb_user', ['level' => $level])->result();
    }

    public function get_detail_user($id, $level)
    {
        return $this->db->get_where('tb_user', ['id_user' => $id, 'level' => $level])->row_array();
    }

    public function get_detail_admin($id, $level)
    {
        $this->db->select('*');
        $this->db->from('tb_user');
        $this->db->where('tb_user.id_user', $id);
        $this->db->where('tb_user.level', $level);
        $this->db->join('tb_admin', "tb_user.id_user = tb_admin.id_user", 'left');
        return $this->db->get()->row_array();
    }

    public function input_data()
    {
        $id_user = $this->_input_admin();
        $data = array(
            'nip'           => $this->input->post('nip', TRUE),
            'nama'          => $this->input->post('nama', TRUE),
            'jenis_kelamin' => $this->input->post('jenis_kelamin', TRUE),
            'tanggal_lahir' => $this->input->post('tanggal_lahir', TRUE),
            'no_hp'         => $this->input->post('no_hp', TRUE),
            'email'         => $this->input->post('email', TRUE),
            'alamat'        => $this->input->post('alamat', TRUE),
            'id_user'       => $id_user
        );

        $this->db->insert('tb_admin', $data);
    }

    public function _input_admin()
    {
        $data = array(
            'username'  => $this->input->post('username', TRUE),
            'password'  => MD5($this->input->post('password', TRUE)),
            'level'     => 'admin',
            'status'    => $this->input->post('status', TRUE)
        );

        $this->db->insert('tb_user', $data);
        return $this->db->insert_id();
    }

    public function cek_user()
    {
        return $this->db->get_where('tb_user', ['username' => $this->input->post('username', TRUE)])->num_rows();
    }

    public function edit_data($id)
    {
        $data = array(
            'status' => $this->input->post('status', TRUE),
        );

        $this->db->where('id_user', $id);
        $this->db->update('tb_user', $data);
    }

    public function edit_admin($id)
    {
        $data_admin = array(
            'nip'           => $this->input->post('nip', TRUE),
            'nama'          => $this->input->post('nama', TRUE),
            'jenis_kelamin' => $this->input->post('jenis_kelamin', TRUE),
            'tanggal_lahir' => $this->input->post('tanggal_lahir', TRUE),
            'no_hp'         => $this->input->post('no_hp', TRUE),
            'email'         => $this->input->post('email', TRUE),
            'alamat'        => $this->input->post('alamat', TRUE)
        );

        $data_account = array(
            'username'  => $this->input->post('username', TRUE),
            'level'     => 'admin',
            'status'    => $this->input->post('status', TRUE)
        );

        $this->db->where('id_user', $id);
        $this->db->update('tb_user', $data_account);

        $this->db->where('id_user', $id);
        $this->db->update('tb_admin', $data_admin);
    }

    public function edit_password($id)
    {
        $data = array(
            'password'  => MD5($this->input->post('password', TRUE)),
        );

        $this->db->where('id_user', $id);
        $this->db->update('tb_user', $data);
    }

    public function delete_data($id)
    {
        $this->db->delete('tb_user', ['id_user' => $id]);
    }

    var $column_order = array(null, 'nama', 'username', 'level', 'status'); //Sesuaikan dengan field
    var $column_search = array('username'); //field yang diizin untuk pencarian 
    var $order = array('id_user' => 'asc'); // default order 

    private function _get_datatables_query($level)
    {

        $table_join = $level == 'admin' ? 'tb_admin' : ($level == 'guru' ? 'tb_guru' : ($level == 'wali kelas' ? 'tb_guru' : ($level == 'siswa' ? 'tb_siswa' : NULL)));

        $this->db->select("tu.id_user, $table_join.nama, tu.username, tu.level, tu.status");
        $this->db->from('tb_user tu');
        $this->db->where('level', $level);
        $this->db->join($table_join, "tu.id_user = $table_join.id_user", 'left');

        $i = 0;

        foreach ($this->column_search as $item) // looping awal
        {
            if ($_POST['search']['value']) // jika datatable mengirimkan pencarian dengan metode POST
            {

                if ($i === 0) // looping awal
                {
                    $this->db->group_start();
                    $this->db->like($item, $_POST['search']['value']);
                } else {
                    $this->db->or_like($item, $_POST['search']['value']);
                }

                if (count($this->column_search) - 1 == $i)
                    $this->db->group_end();
            }
            $i++;
        }

        if (isset($_POST['order'])) {
            $this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->db->order_by(key($order), $order[key($order)]);
        }
    }

    function get_datatables($level)
    {
        $this->_get_datatables_query($level);
        if ($_POST['length'] != -1)
            $this->db->limit($_POST['length'], $_POST['start']);
        $query = $this->db->get();
        return $query->result();
    }

    function count_filtered($level)
    {
        $this->_get_datatables_query($level);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all($level)
    {
        $this->db->from('tb_user');
        $this->db->where('level', $level);
        return $this->db->count_all_results();
    }
}
