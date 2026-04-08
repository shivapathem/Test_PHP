USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_StaffDetailsUserDetailsByLogin]    Script Date: 25/11/2022 13:24:03 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER   PROCEDURE [dbo].[usp_get_StaffDetailsUserDetailsByLogin] 
    @netlogin VARCHAR(500)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
	DECLARE @status	 INT ,@returnstring VARCHAR(1000);
	BEGIN TRY
	SELECT sd.StaffID,sd.StaffNumber,CASE WHEN sp.ScheduledPersonID IS NULL THEN CASE WHEN (sd.PreferredForename IS NULL or sd.PreferredForename = '')
                THEN CASE WHEN (sd.Surname IS NULL or sd.Surname = '') THEN  sd.Forename ELSE (sd.Forename + ' ' +sd.Surname  )END  ELSE
				CASE WHEN (sd.Surname IS NULL or sd.Surname = '') THEN  sd.PreferredForename ELSE (sd.PreferredForename + ' ' +sd.Surname  ) END   END
        ELSE CASE WHEN (sp.DisplayLastName IS NULL or sp.DisplayLastName = '') THEN sp.DisplayFirstName ELSE (sp.DisplayFirstName + ' '+ sp.DisplayLastName) END END AS userDisplayName ,u.UserID 
		FROM StaffDetails  sd (nolock)
		INNER JOIN Users u (nolock) on u.NetLogin = sd.NetLogin 
		LEFT JOIN ScheduledPeople sp (nolock) on sp.StaffDetailsID = sd.StaffID where sd.NetLogin = ltrim(@netlogin)
	END TRY
	BEGIN CATCH
		INSERT INTO ErrorLog
			VALUES
			(
			ERROR_NUMBER(),
			ERROR_STATE(),
			ERROR_SEVERITY(),
			ERROR_LINE(),
			'usp_get_StaffDetailsUserDetailsByLogin',
			ERROR_MESSAGE(),
			GETDATE(),
			@netlogin
			)
	END CATCH
END
