USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_GetNameAndEFTFromStaffNumber]    Script Date: 31/10/2025 19:16:08 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		HCL
-- Create date: 08-05-2022
-- Description:	used to get EFT of staff
-- =============================================
CREATE OR ALTER      PROCEDURE [dbo].[usp_GetNameAndEFTFromStaffNumber] 
	@scheduledPersonId INT, 
	@teamId INT
AS
BEGIN

	SET NOCOUNT ON;

    select SP.UD_DisplayName Fullname, 
	       SCP.UC_EFT EFT
	  from UserDetails SP WITH (NOLOCK)
	 INNER JOIN ScheduledPersonTeam_LINK SPTL  WITH (NOLOCK) ON SPTL.ScheduledPersonID = SP.UD_UserID
	  LEFT JOIN UserConfigs SCP  WITH (NOLOCK) ON SCP.UC_UserID = SP.UD_UserID 
			AND GETDATE() between SCP.UC_StartDate AND SCP.UC_EndDate 
	  WHERE SPTL.TeamID = @teamId  
	    AND SPTL.scheduledType = 1 
		AND GETDATE() between SPTL.StartDate AND SPTL.EndDate 
		AND SP.UD_UserID = @scheduledPersonId
END