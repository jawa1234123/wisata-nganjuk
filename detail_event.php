<?php

require_once __DIR__ . '/../services/EventService.php';
require_once __DIR__ . '/../views/EventDetailView.php';

/**
 * EventDetailController
 * 
 * Mengorkestrasi alur request untuk halaman detail event.
 * 
 * Prinsip SOLID yang diterapkan:
 * - SRP : hanya bertanggung jawab mengarahkan request ke service
 *         dan meneruskan hasilnya ke view
 * - DIP : menerima EventService & EventDetailView via constructor
 *         (dependency injection), bukan di-instantiate di dalam
 * - OCP : untuk entitas lain, buat controller baru; class ini
 *         tidak perlu diubah
 */
class EventDetailController
{
    private EventService    $service;
    private EventDetailView $view;

    public function __construct(
        EventService    $service,
        EventDetailView $view
    ) {
        $this->service = $service;
        $this->view    = $view;
    }

    /**
     * Tampilkan halaman detail event.
     * 
     * @param mixed $rawId  ID mentah dari $_GET['id']
     */
    public function show(mixed $rawId): void
    {
        try {
            $data = $this->service->getEventById($rawId);
            $this->view->render($data);

        } catch (InvalidArgumentException $e) {
            // ID tidak valid (kosong, bukan angka, dll)
            $this->view->renderError($e->getMessage());

        } catch (RuntimeException $e) {
            // Data tidak ditemukan di database
            $this->view->renderNotFound();
        }
    }
}
