import { ReactNode } from "react"

type ListWrapperProps = {
  title: string,
  actions: ReactNode,
  children: ReactNode,
}

export default function ListWrapper({title, actions, children}: ListWrapperProps) {
 return (
    <div className="flex flex-col h-full">
      <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white shrink-0">
        <h1 className="text-lg font-semibold text-gray-900">{title}</h1>
        <div className="flex items-center gap-2">{actions}</div>
      </div>
      <div className="flex-1 overflow-y-auto p-6">
        {children}
      </div>
    </div>
 )
}