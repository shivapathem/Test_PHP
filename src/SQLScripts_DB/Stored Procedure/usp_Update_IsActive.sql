USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_Update_IsActive]    Script Date: 15/08/2022 00:11:39 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO


-- ============================================= 
-- Author:		<HCL>
-- Create date: <17-Mar-2022>
-- Description:	This SP is used to update IsActive flag from Teampay to A7
-- =============================================
CREATE OR ALTER PROCEDURE [dbo].[usp_Update_IsActive] 
	
AS
BEGIN

	Update A7SC
		SET A7SC.IsActive=TPSC.IsActive
		FROM Allocate7.dbo.StaffConfig A7SC JOIN Allocate7.dbo.StaffDetails A7SD ON A7SD.StaffID=A7SC.StaffID
		JOIN AllocateLink.dbo.TP_A7_StaffDetails TPSD ON TPSD.NetLogin=A7SD.NetLogin
		JOIN AllocateLink.dbo.TP_A7_StaffConfig TPSC ON TPSD.StaffID=TPSC.StaffID
              AND A7SC.StartDate=TPSC.StartDate



	Update A7SC
		SET A7SC.IsActive=TPSC.IsActive
		FROM Allocate7.dbo.StaffContract A7SC JOIN Allocate7.dbo.StaffDetails A7SD ON A7SD.StaffID=A7SC.StaffID
		JOIN AllocateLink.dbo.TP_A7_StaffDetails TPSD ON TPSD.NetLogin=A7SD.NetLogin
		JOIN AllocateLink.dbo.TP_A7_StaffContract TPSC ON TPSD.StaffID=TPSC.StaffID
              AND A7SC.StartDate=TPSC.StartDate

END