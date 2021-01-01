<?php
class Nilai extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        if (!isset($this->session->userdata['username'])) {
            $this->session->set_flashdata('message', 'Anda Belum Login!');
            redirect('login');
        }

        if ($this->session->userdata['level'] != 'wali kelas') {
            $this->session->set_flashdata('message', 'Anda Belum Login!');
            redirect('login');
        }
    }

    public function index()
    {
        $data = $this->User_model->get_detail_guru($this->session->userdata['id_user'], $this->session->userdata['level']);
        $kelas = $this->Kelas_model->get_like_walikelas($data['nama']);
        $data = array(
            'id_user'   => $data['id_user'],
            'nama'      => $data['nama'],
            'photo'     => $data['photo'] != null ? $data['photo'] : 'user-placeholder.jpg',
            'level'     => $data['level'],
            'tahun'     => $this->Tahun_model->get_active_stats(),
            'kelas'     => $kelas,
            'mapel'     => $this->Mapel_model->get_mapel_with_kelas($kelas['id_kelas']),
            'menu'      => 'nilai',
            'breadcrumb' => [
                0 => (object)[
                    'name' => 'Dashboard',
                    'link' => 'walikelas'
                ],
                1 => (object)[
                    'name' => 'Nilai',
                    'link' => NULL
                ]
            ]
        );

        $this->load->view('templates/header');
        $this->load->view('templates_guruwali/sidebar', $data);
        $this->load->view('guru_wali/nilai', $data);
        $this->load->view('templates/footer');
    }

    public function data_nilai_permapel()
    {
        $id_kelas       = $this->input->post('id_kelas', TRUE);
        $id_mapel       = $this->input->post('id_mapel', TRUE);
        $kelas          = $this->Kelas_model->get_detail_data($id_kelas);
        $mapel          = $this->Mapel_model->get_detail_data($id_mapel);
        $data_default   = $this->Nilai_model->get_nilai_permapel($id_mapel, $id_kelas, 'default');
        $data_min       = $this->Nilai_model->get_nilai_permapel($id_mapel, $id_kelas, 'min');
        $data_max       = $this->Nilai_model->get_nilai_permapel($id_mapel, $id_kelas, 'max');
        $data_jumlah    = $this->Nilai_model->get_nilai_permapel($id_mapel, $id_kelas, 'jumlah');
        $data_rerata    = $this->Nilai_model->get_nilai_permapel($id_mapel, $id_kelas, 'rerata');
        $daftar_kd      = $this->Nilai_model->get_kd_permapel_result($id_mapel, $id_kelas);
        $html           = '';

        if ($id_mapel == null || $id_kelas == null) {
            //id not found
            $html = $html . '<div class="card">
                                <div class="card-body">
                                    <h6 class="text-center">Data Nilai Tidak Tersedia</h6>
                                </div>
                            </div>';
        } else if ($data_default != null) {
            //awal table
            $html = $html . '<div class="card">
                    <div class="card-header bg-behance">
                        <h6 class="text-white"> ' . $mapel['nama_mapel'] . ' / Kelas ' . $kelas['kelas'] . '</h6>
                    </div>
                    <div class="card-body">
                        <a href="' . base_url('walikelas/nilai/kd?id_kelas=' . $id_kelas . '&id_mapel=' . $id_mapel) . '" class="btn btn-primary mb-3"><i class="fas fa-info-circle"></i> Cek Selengkapnya</i></a>
                        <table class="table table-responsive-sm table-bordered table-striped table-sm w-100 d-block d-md-table">
                            <thead>
                                <tr>
                                    <th style="vertical-align : middle;text-align:center;">No</th>
                                    <th style="vertical-align : middle;text-align:center;">Nama</th>';

            //heading table
            foreach ($daftar_kd as $key => $value) {
                $html = $html . '<th style="vertical-align : middle;text-align:center;">' . $value->nama_kd . '</th>';
            }

            //jumlah dan rata-rata
            $html = $html . '<th style="vertical-align : middle;text-align:center;">Jumlah</th>
                            <th style="vertical-align : middle;text-align:center;">Rata-rata</th>
                            </tr></thead><tbody>';

            //body table default
            foreach ($data_default as $dt => $value_dt) {
                $html = $html . '<tr>
                    <td width="20px">' . ++$dt . '</td>
                    <td>' . $value_dt['nama'] . '</td>';
                foreach ($daftar_kd as $kd => $value_kd) {
                    $html = $html . '<td>' . $value_dt[$value_kd->nama_kd] . '</td>';
                }

                $html = $html . "<td>{$value_dt['jumlah']}</td><td>{$value_dt['rerata']}</td></tr>";
            }

            //body table min
            foreach ($data_min as $dt => $value_dt) {
                $html = $html . '<tr>
                    <td width="20px"></td>
                    <td></td>';
                foreach ($daftar_kd as $kd => $value_kd) {
                    $html = $html . '<td></td>';
                }

                $html = $html . "<td></td><td></td></tr>";

                $html = $html . '<tr>
                    <td width="20px"></td>
                    <td>MIN</td>';
                foreach ($daftar_kd as $kd => $value_kd) {
                    $html = $html . '<td>' . $value_dt[$value_kd->nama_kd] . '</td>';
                }

                $html = $html . "<td>{$value_dt['jumlah']}</td><td>{$value_dt['rerata']}</td></tr>";
            }

            //body table max
            foreach ($data_max as $dt => $value_dt) {
                $html = $html . '<tr>
                    <td width="20px"></td>
                    <td>MAX</td>';
                foreach ($daftar_kd as $kd => $value_kd) {
                    $html = $html . '<td>' . $value_dt[$value_kd->nama_kd] . '</td>';
                }

                $html = $html . "<td>{$value_dt['jumlah']}</td><td>{$value_dt['rerata']}</td></tr>";
            }

            //body table jumlah
            foreach ($data_jumlah as $dt => $value_dt) {
                $html = $html . '<tr>
                    <td width="20px"></td>
                    <td>JUMLAH</td>';
                foreach ($daftar_kd as $kd => $value_kd) {
                    $html = $html . '<td>' . $value_dt[$value_kd->nama_kd] . '</td>';
                }

                $html = $html . "<td>{$value_dt['jumlah']}</td><td>{$value_dt['rerata']}</td></tr>";
            }

            //body table rerata
            foreach ($data_rerata as $dt => $value_dt) {
                $html = $html . '<tr>
                    <td width="20px"></td>
                    <td>RATA-RATA</td>';
                foreach ($daftar_kd as $kd => $value_kd) {
                    $html = $html . '<td>' . $value_dt[$value_kd->nama_kd] . '</td>';
                }

                $html = $html . "<td>{$value_dt['jumlah']}</td><td>{$value_dt['rerata']}</td></tr>";
            }

            //akhir table
            $html = $html . '</tbody></table></div></div>';
        } else {
            $html = $html . '
                <div class="card">
                    <div class="card-header bg-behance">
                        <h6 class="text-white"> ' . $mapel['nama_mapel'] . ' / Kelas ' . $kelas['kelas'] . '</h6>
                    </div>
                    <div class="card-body">
                        <a href="' . base_url('walikelas/nilai/kd?id_kelas=' . $id_kelas . '&id_mapel=' . $id_mapel) . '" class="btn btn-primary mb-3"><i class="fas fa-info-circle"></i> Cek Selengkapnya</i></a>
                        <h6 class="text-center">Data nilai belum lengkap, silahkan cek selengkapnya</h6>
                    </div>
                </div>
            
            ';
        }

        echo ($html);
    }

    public function kd()
    {
        $id_kelas = $this->input->get('id_kelas', TRUE);
        $id_mapel = $this->input->get('id_mapel', TRUE);

        if (!isset($id_kelas) || !isset($id_mapel)) {
            redirect('error_404');
        }

        $data = $this->User_model->get_detail_guru($this->session->userdata['id_user'], $this->session->userdata['level']);
        $data = array(
            'id_user'   => $data['id_user'],
            'nama'      => $data['nama'],
            'photo'     => $data['photo'] != null ? $data['photo'] : 'user-placeholder.jpg',
            'level'     => $data['level'],
            'id_kelas'  => $id_kelas,
            'id_mapel'  => $id_mapel,
            'kelas'     => $this->Kelas_model->get_detail_data($id_kelas),
            'mapel'     => $this->Mapel_model->get_detail_data($id_mapel),
            'komp_dasar' => $this->Mapel_model->get_mapel_with_kd_detail($id_mapel, $id_kelas),
            'menu'      => 'nilai',
            'breadcrumb' => [
                0 => (object)[
                    'name' => 'Dashboard',
                    'link' => 'walikelas'
                ],
                1 => (object)[
                    'name' => 'Nilai',
                    'link' => 'walikelas/nilai'
                ],
                2 => (object)[
                    'name' => 'Detail',
                    'link' => NULL
                ]
            ]
        );

        $this->load->view('templates/header');
        $this->load->view('templates_guruwali/sidebar', $data);
        $this->load->view('guru_wali/nilai_perkd', $data);
        $this->load->view('templates/footer');
    }

    public function data_nilai_perkd()
    {
        $id_kelas   = $this->input->post('id_kelas', TRUE);
        $id_mapel   = $this->input->post('id_mapel', TRUE);
        $id_kd      = $this->input->post('id_kd', TRUE);
        $data       = $this->Nilai_model->get_nilai_perkd($id_kelas, $id_mapel, $id_kd);
        $jenis      = $this->Nilai_model->get_jenis_nilai_in_perkd($id_kelas, $id_mapel, $id_kd);
        $kd         = $this->Mapel_model->get_kd_detail($id_kd);
        $html       = '';
        if ($data != null || $jenis != null) {
            //awal table
            $html = $html . '<div class="card">
                    <div class="card-body">
                        ' . anchor('walikelas/nilai/input?id_kelas=' . $id_kelas . '&id_mapel=' . $id_mapel . '&id_kd=' . $id_kd, '<button class="btn btn-sm btn-primary mb-3 mr-2"><i class="fas fa-plus fa-sm"></i> Tambah Nilai</button>') . '
                        <h5>' . $kd['nama_kd'] . '</h5>
                        <table class="table table-responsive-sm table-bordered table-striped table-sm w-100 d-block d-md-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="vertical-align : middle;text-align:center;" >No</th>
                                    <th rowspan="2" style="vertical-align : middle;text-align:center;" >Nama</th>';

            //heading button table
            foreach ($jenis as $jn => $value) {
                $html = $html . '<th class="text-center">' .
                    anchor('walikelas/nilai/edit?id_kelas=' . $id_kelas . '&id_mapel=' . $id_mapel . '&id_kd=' . $id_kd . '&jenis=' . $value->jenis, '<div class="btn btn-sm btn-primary mr-1 ml-1 mb-1"><i class="fa fa-edit fa-sm"></i></div>') .
                    '<a href="' . base_url('walikelas/nilai/delete?id_kelas=' . $id_kelas . '&id_mapel=' . $id_mapel . '&id_kd=' . $id_kd . '&jenis=' . $value->jenis) . '" class="btn btn-sm btn-danger mr-1 ml-1 mb-1" onclick="return deleteNilai(event)"><i class="fa fa-trash fa-sm"></i></a>' .
                    '</th>';
            }

            $html = $html . '<th rowspan="2" style="vertical-align : middle;text-align:center;">Jumlah</th>
                            <th rowspan="2" style="vertical-align : middle;text-align:center;">Rata-rata</th>
                            </tr><tr>';

            //heading table
            foreach ($jenis as $jn => $value) {
                $html = $html . '<th>' . $value->jenis . '</th>';
            }

            $html = $html . '</tr></thead><tbody>';

            //body table
            foreach ($data as $dt => $value_dt) {
                $html = $html . '<tr>
                    <td width="20px">' . ++$dt . '</td>
                    <td>' . $value_dt['nama'] . '</td>';
                foreach ($jenis as $jn => $value_jn) {
                    $html = $html . '<td>' . $value_dt[$value_jn->jenis] . '</td>';
                }

                $html = $html . "<td>{$value_dt['jumlah']}</td><td>{$value_dt['rerata']}</td></tr>";
            }

            //akhir table
            $html = $html . '</tbody></table></div></div>';
        } else if ($id_mapel == null || $id_kelas == null || $id_kd == null) {
            //id not found
            $html = $html . '<div class="card">
                                <div class="card-body">
                                    <h6 class="text-center">Data Nilai Tidak Dapat Ditampilkan, Silahkan Pilih Kompetensi Dasar</h6>
                                </div>
                            </div>';
        } else {
            //data not found
            $html = $html . '<div class="card">
                                <div class="card-body">
                                    ' . anchor('walikelas/nilai/input?id_kelas=' . $id_kelas . '&id_mapel=' . $id_mapel . '&id_kd=' . $id_kd, '<button class="btn btn-sm btn-primary mb-3 mr-2"><i class="fas fa-plus fa-sm"></i> Tambah Nilai</button>') . '
                                    <h5>' . $kd['nama_kd'] . '</h5>
                                    <h5 class="text-center">Data nilai ' . $kd['nama_kd'] . ' belum tersedia, silahkan klik tambah nilai untuk menambahkan nilai siswa</h5>
                                </div>
                            </div>';
        }
        echo ($html);
    }

    // input nilai
    public function input()
    {
        $id_kelas   = $this->input->get('id_kelas', TRUE);
        $id_mapel   = $this->input->get('id_mapel', TRUE);
        $id_kd      = $this->input->get('id_kd', TRUE);

        if (!isset($id_kelas) || !isset($id_mapel) || !isset($id_kd)) {
            redirect('error_404');
        }

        $result_jenis = array_column($this->Nilai_model->get_jenis_nilai_in_perkd_array($id_kelas, $id_mapel, $id_kd), 'jenis');
        $object_jenis = ['Tugas Harian 1', 'Tugas Harian 2', 'Tugas Harian 3', 'Tugas Harian 4', 'Ulangan Harian 1', 'Ulangan Harian 2', 'Ulangan Harian 3', 'Ulangan Harian 4', 'UTS', 'UAS'];
        $data = $this->User_model->get_detail_admin($this->session->userdata['id_user'], $this->session->userdata['level']);
        $data = array(
            'id_user'   => $data['id_user'],
            'nama'      => $data['nama'],
            'photo'     => $data['photo'] != null ? $data['photo'] : 'user-placeholder.jpg',
            'level'     => $data['level'],
            'id_kelas'  => $id_kelas,
            'id_mapel'  => $id_mapel,
            'kelas'     => $this->Kelas_model->get_detail_data($id_kelas),
            'mapel'     => $this->Mapel_model->get_detail_data($id_mapel),
            'komp_dasar' => $this->Mapel_model->get_kd_detail($id_kd),
            'pengajar'  => $this->Pengajar_model->get_detail_data_with_kelas_and_mapel($id_kelas, $id_mapel),
            'siswa'     => $this->Siswa_model->get_data_perkelas($id_kelas),
            'jenis_nilai' => array_diff($object_jenis, $result_jenis),
            'menu'      => 'nilai',
            'breadcrumb' => [
                0 => (object)[
                    'name' => 'Dashboard',
                    'link' => 'walikelas'
                ],
                1 => (object)[
                    'name' => 'Nilai',
                    'link' => 'walikelas/nilai'
                ],
                2 => (object)[
                    'name' => 'Detail',
                    'link' => 'walikelas/nilai/kd?id_kelas=' . $id_kelas . '&id_mapel=' . $id_mapel
                ],
                3 => (object)[
                    'name' => 'Input Nilai',
                    'link' => NULL
                ]
            ]
        );

        $this->_rules_persiswa($data['siswa']);

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('templates/header');
            $this->load->view('templates_guruwali/sidebar', $data);
            $this->load->view('guru_wali/nilai_input', $data);
            $this->load->view('templates/footer');
        } else {
            $this->Nilai_model->input_nilai($data['siswa'], $id_kd);
            $this->session->set_flashdata('message', 'Nilai Siswa Berhasil Ditambahkan!');
            redirect('walikelas/nilai/kd?id_kelas=' . $id_kelas . '&id_mapel=' . $id_mapel);
        }
    }

    public function delete()
    {
        $id_kelas   = $this->input->get('id_kelas', TRUE);
        $id_kd      = $this->input->get('id_kd', TRUE);
        $jenis      = $this->input->get('jenis', TRUE);
        $id_mapel   = $this->input->get('id_mapel', TRUE);

        if (!isset($id_kelas) || !isset($id_kd) || !isset($jenis) || !isset($id_mapel)) {
            redirect('error_404');
        }

        $this->Nilai_model->delete_nilai($id_kelas, $id_kd, $jenis);
        $this->session->set_flashdata('message', 'Data Nilai Berhasil Dihapus!');
        redirect('walikelas/nilai/kd?id_kelas=' . $id_kelas . '&id_mapel=' . $id_mapel);
    }

    public function _rules_persiswa($data_siswa)
    {
        foreach ($data_siswa as $key => $value) {
            $this->form_validation->set_rules('nilai' . $key, 'Nilai', 'required|numeric');
        }
        $this->form_validation->set_rules('jenis', 'Jenis Nilai', 'required');
    }
}
