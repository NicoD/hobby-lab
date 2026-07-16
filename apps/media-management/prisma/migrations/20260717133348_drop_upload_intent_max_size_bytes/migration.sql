/*
  Warnings:

  - You are about to drop the column `maxSizeBytes` on the `UploadIntent` table. All the data in the column will be lost.

*/
-- AlterTable
ALTER TABLE "UploadIntent" DROP COLUMN "maxSizeBytes";
