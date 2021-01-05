<?php
class LaporanNilai extends CI_Controller
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
            'kelas'     => $kelas,
            'tahun'     => $this->Tahun_model->get_data(),
            'menu'      => 'laporan_nilai',
            'breadcrumb' => [
                0 => (object)[
                    'name' => 'Dashboard',
                    'link' => 'walikelas'
                ],
                1 => (object)[
                    'name' => 'Laporan Daftar Nilai',
                    'link' => NULL
                ]
            ]
        );

        $this->load->view('templates/header');
        $this->load->view('templates_guruwali/sidebar', $data);
        $this->load->view('guru_wali/laporan_nilai', $data);
        $this->load->view('templates/footer');
    }

    public function data_all_nilai()
    {
        $id_tahun       = $this->input->post('id_tahun', TRUE);
        $id_kelas       = $this->input->post('id_kelas', TRUE);
        $tahun          = $this->Tahun_model->get_detail_data($id_tahun);
        $kelas          = $this->Kelas_model->get_detail_data($id_kelas);
        $daftar_mapel   = $this->Laporan_model->get_mapel_pertahun($id_tahun, $id_kelas)->result();
        $result         = $this->Laporan_model->get_data_nilai($id_tahun, $id_kelas, 'default');
        $result_min     = $this->Laporan_model->get_data_nilai($id_tahun, $id_kelas, 'min');
        $result_max     = $this->Laporan_model->get_data_nilai($id_tahun, $id_kelas, 'max');
        $result_jumlah  = $this->Laporan_model->get_data_nilai($id_tahun, $id_kelas, 'jumlah');
        $result_rerata  = $this->Laporan_model->get_data_nilai($id_tahun, $id_kelas, 'rerata');
        $html           = '';

        if ($result != null) {
            $html = $html . '
                <div class="card">
                    <div class="card-body">
                        <a href="' . base_url('walikelas/laporannilai/pdf_laporan?q=alldata&tahun=' . $id_tahun . '&kelas=' . $id_kelas) . '" class="btn btn-info mb-2"><i class="fas fa-print"></i> Print PDF</a>
                        <a href="' . base_url('walikelas/laporannilai/pdf_laporan?q=alldata&tahun=' . $id_tahun . '&kelas=' . $id_kelas) . '" class="btn btn-success mb-2"><i class="fas fa-file-excel"></i> Print Excel</a>
                        <div>
                            <h1 class="h1 text-center">LAPORAN DAFTAR NILAI SISWA</h1>
                            <h2 class="text-center">SD MUHAMMADIYAH TRINI</h2>
                            <h3 class="text-center">Tahun Ajaran ' . $tahun['nama'] . '</h3>
                            <h4 class="text-center">Kelas ' . $kelas['kelas'] . '</h4>
                        </div>
                        <table class="table table-responsive-sm table-bordered table-striped table-sm w-100 d-block d-md-table" id="table-laporansiswa">
                            <thead>
                                <tr class="text-center">
                                    <th width="10px">NO</th>
                                    <th width="10px">NIS</th>
                                    <th width="10px">NISN</th>
                                    <th>NAMA</th>';

            //heading mapel
            foreach ($daftar_mapel as $key => $value) {
                $html = $html . "<th>$value->nama_mapel</th>";
            }

            //heading jumlah dan rata-rata
            $html = $html . '<th>Jumlah</th>
                            <th>Rata-rata</th>
                            </tr></thead><tbody>';

            // body table default
            foreach ($result as $key => $value) {
                $html = $html . '
                    <tr>
                        <td class="text-center">' . ++$key . '</td>
                        <td>' . $value['nis'] . '</td>
                        <td>' . $value['nisn'] . '</td>
                        <td>' . $value['nama'] . '</td>';

                foreach ($daftar_mapel as $kd => $mapel) {
                    $html = $html . '<td>' . $value[$mapel->nama_mapel] . '</td>';
                }

                $html = $html . '
                        <td>' . $value['jumlah'] . '</td>
                        <td>' . $value['rerata'] . '</td>
                    </tr>';
            }

            // body table min
            foreach ($result_min as $key => $value) {
                $html = $html . '<tr><td colspan="100%"></td></tr>';

                $html = $html . '<tr>
                    <td width="20px"></td>
                    <td colspan="3">MIN</td>';
                foreach ($daftar_mapel as $kd => $mapel) {
                    $html = $html . '<td>' . $value[$mapel->nama_mapel] . '</td>';
                }

                $html = $html . "<td>{$value['jumlah']}</td><td>{$value['rerata']}</td></tr>";
            }

            // body table max
            foreach ($result_max as $key => $value) {
                $html = $html . '<tr>
                    <td width="20px"></td>
                    <td colspan="3">MAX</td>';
                foreach ($daftar_mapel as $kd => $mapel) {
                    $html = $html . '<td>' . $value[$mapel->nama_mapel] . '</td>';
                }

                $html = $html . "<td>{$value['jumlah']}</td><td>{$value['rerata']}</td></tr>";
            }

            // body table jumlah
            foreach ($result_jumlah as $key => $value) {
                $html = $html . '<tr>
                    <td width="20px"></td>
                    <td colspan="3">Jumlah</td>';
                foreach ($daftar_mapel as $kd => $mapel) {
                    $html = $html . '<td>' . $value[$mapel->nama_mapel] . '</td>';
                }

                $html = $html . "<td>{$value['jumlah']}</td><td>{$value['rerata']}</td></tr>";
            }

            // body table rerata
            foreach ($result_rerata as $key => $value) {
                $html = $html . '<tr>
                    <td width="20px"></td>
                    <td colspan="3">Rata-Rata</td>';
                foreach ($daftar_mapel as $kd => $mapel) {
                    $html = $html . '<td>' . $value[$mapel->nama_mapel] . '</td>';
                }

                $html = $html . "<td>{$value['jumlah']}</td><td>{$value['rerata']}</td></tr>";
            }

            $html = $html . '<tr></tr>';

            $html = $html . '
                            </tbody>
                        </table>
                    </div>
                </div>';
        } else {
            $html = $html . '<div class="card">
                                <div class="card-body">
                                    <h6 class="text-center">Laporan Daftar Nilai Siswa Tidak Tersedia, Silahkan Masukan Data Yang Diperlukan</h6>
                                </div>
                            </div>';
        }

        echo $html;
    }

    public function pdf_laporan()
    {
        $query      = $this->input->get('q');
        $id_tahun      = $this->input->get('tahun');
        $id_kelas      = $this->input->get('kelas');

        if ($query == 'alldata') {
            $data['tahun']   = $this->Tahun_model->get_detail_data($id_tahun);
            $data['kelas']   = $this->Kelas_model->get_detail_data($id_kelas);
            $data['mapel']   = $this->Laporan_model->get_mapel_pertahun($id_tahun, $id_kelas)->result();
            $data['result']  = $this->Laporan_model->get_data_nilai($id_tahun, $id_kelas, 'default');
            $data['min']     = $this->Laporan_model->get_data_nilai($id_tahun, $id_kelas, 'min');
            $data['max']     = $this->Laporan_model->get_data_nilai($id_tahun, $id_kelas, 'max');
            $data['jumlah']  = $this->Laporan_model->get_data_nilai($id_tahun, $id_kelas, 'jumlah');
            $data['rerata']  = $this->Laporan_model->get_data_nilai($id_tahun, $id_kelas, 'rerata');

            $this->mypdf->generate('pdf/laporan_allnilai', $data, 'Laporan Data Siswa', 'A4', 'landscape');
            // $this->load->view('pdf/laporan_allnilai', $data);
        }
    }
}
