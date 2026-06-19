import FormAddPaint from "../../components/ColorLab/FormAddPaint";
import ListWrapper from "../../components/ListWrapper";
import Modal from "../../components/Modal";
import useModal from "../../hooks/useModal";

export default function Paint() {

  const addModal = useModal()

  return (
    <>
        <ListWrapper
        title="Peintures"
        actions={
            <button
            onClick={addModal.toggle}
            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 active:bg-indigo-800 transition-colors"
            >
            <svg xmlns="http://www.w3.org/2000/svg" className="w-4 h-4" fill="none" viewBox="0 0 24 24" strokeWidth={2.5} stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Ajouter
            </button>
        }
        >
        TODO
        </ListWrapper>
        <Modal title="Ajouter" isOpen={addModal.isOpen} close={addModal.close}>
            <FormAddPaint />
        </Modal>
    </>
  )
}