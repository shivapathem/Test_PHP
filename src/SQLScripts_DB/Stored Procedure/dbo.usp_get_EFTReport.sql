USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_EFTReport]    Script Date: 05/01/2026 21:39:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO



CREATE OR ALTER        PROCEDURE [dbo].[usp_get_EFTReport]
	@DepartmentID	INT = NULL
AS

BEGIN
SET NOCOUNT ON;

				Select  	StaffID,
							DisplayName, 
							Department,
							Startdate,
							EndDate as Endate, 
							PartTimeEDP,
							EDPMinimumExcBreaks,
							Week,
							ContractEFT,
							ConfigEFT,
							TermTimeHours,
							IsLeaver 
				FROM EFTDescrepancies WHERE Departmentid=@DepartmentID
				AND  abs(ContractEFT-ConfigEFT)>0.01

				

END


