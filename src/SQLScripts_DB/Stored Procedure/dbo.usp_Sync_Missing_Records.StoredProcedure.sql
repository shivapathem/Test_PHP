USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_Sync_Missing_Records]    Script Date: 30/12/2025 13:41:02 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER     PROCEDURE [dbo].[usp_Sync_Missing_Records] 
	@SCP_ID NVARCHAR(MAX),
	@UserId INT
AS
BEGIN

DECLARE @SCPID INT,
		@StartDate DATE,
		@StaffID INT,
		@A7StaffID INT,
		@A7ID INT,
		@NetLogin NVARCHAR(20),
		@username VARCHAR(100), 
		@history VARCHAR(MAX),
		@DataList CURSOR;

BEGIN TRY

		SELECT @username = UD_DisplayName 
		  FROM UserDetails 
		 WHERE UD_UserID = @UserId 

		SET @history = 'Imported into TP Integration Page on '
						+FORMAT (getdate(), 'dd/MM/yyyy hh:mm')
						+' by A7.  Approved By '
						+@username
						+ ' On ' + FORMAT (getdate(), 'dd/MM/yyyy') 
						+ ' At ' + FORMAT (getdate(), 'hh:mm')  
						+' in Missing Records tab.'

	SET @DataList = CURSOR FOR
					SELECT SCP_ID, 
						   StartDate
					  FROM StaffConfig_Processed
					 WHERE SCP_ID IN (SELECT Value FROM STRING_SPLIT(@SCP_ID, ','))

	OPEN @DataList
	
	FETCH NEXT
	
	FROM @DataList INTO @SCPID, @StartDate
	
	WHILE @@FETCH_STATUS = 0
	 BEGIN

		SET @StaffID = 0
		SET @A7ID = 0

		SELECT @NetLogin= Netlogin,
			   @StaffID = SD.StaffID,
			   @A7ID = UD_UserID
		  from StaffDetails SD
		 INNER JOIN StaffConfig_Processed SCP ON SCP.StaffID = SD.StaffID
		 LEFT JOIN UserDetails UD ON UD_TeampayStaffID = SD.StaffID
		  where SCP.SCP_ID = @SCPID

		IF (ISNULL(@A7ID, 0) = 0 AND ISNULL(@StaffID,0) > 0 )
		 BEGIN

			SELECT @A7ID = UD_UserID,
				   @A7StaffID = UD_TeampayStaffID
			  FROM UserDetails 
			 WHERE UD_NetLogin = @NetLogin

		   IF ( ISNULL(@A7ID,0) > 0 AND ISNULL(@A7StaffID,0) = 0 )
		    BEGIN

			 UPDATE UD
				SET	UD_EmpNumber = sd.EmpNumber,
					UD_StaffNumber = sd.StaffNumber,
					UD_InternalEmail = sd.InternalEmail,
					UD_ExternalEmail = sd.ExternalEmail,
					UD_PersonalPhone = sd.AltTelephone,
					UD_TeampayStaffID = sd.StaffID,
					UD_StartDate = sd.JoinDate,
					UD_OfficePhone = sd.OfficeMobile,
					UD_OfficeExtension = sd.OfficeExtension	
			   FROM UserDetails UD
			  INNER JOIN StaffDetails SD ON UD.UD_NetLogin = SD.NetLogin
			  WHERE UD_UserID = @A7ID
			    AND SD.StaffID = @StaffID
						
			END

			IF ( ISNULL(@A7ID,0) = 0 )
			 BEGIN			 						
	
				INSERT INTO UserDetails(
							UD_EmpNumber,
							UD_StaffNumber,
							UD_NetLogin,
							UD_DisplayName,
							UD_DisplayFirstName,
							UD_DisplayLastName,
							UD_PreferredFirstName,
							UD_InternalEmail,
							UD_ExternalEmail,
							UD_PersonalPhone,
							UD_AdminNotes,
							UD_FWANotes,
							UD_PHLLeaveAmount,
							UD_TeampayStaffID,
							UD_OfficePhone,
							UD_OfficeExtension,
							UD_StartDate,
							UD_Status,
							UD_IsEligibleForAdditionalLeave,
							UD_CreatedBy,
							UD_CreatedDate)
					 SELECT sd.EmpNumber,
							sd.StaffNumber,
							sd.NetLogin,
							sd.Forename+' '+sd.Surname as DisplayName,
							sd.Forename AS DisplayFirstname,
							sd.Surname	AS DisplayLastname,
							sd.PreferredForename,
							sd.InternalEmail,
							sd.ExternalEmail,
							sd.AltTelephone PersonalPhone,
							NULL AS AdminNotes,
							NULL as FWANotes,
							0,
							sd.StaffID,
							sd.OfficeMobile,
							sd.OfficeExtension,
							sd.JoinDate,
							1,
							0,
							@UserId,
							GETDATE()
					   FROM StaffDetails sd
					  WHERE sd.StaffID = @StaffID

					  SET @A7ID = @@IDENTITY
			  END

		 END

		IF (ISNULL(@A7ID, 0) > 0 )
		 BEGIN		 		
		  IF NOT EXISTS ( SELECT 1 
							FROM UserConfigs
						   WHERE UC_UserID = @A7ID 
						     AND UC_SCP_ID = @SCPID )
		   BEGIN
			
			 INSERT INTO UserConfigs (UC_UserID,
							UC_EFT,
							UC_CostCode,
							UC_AccGroupID,
							UC_AccGroup,
							UC_PaymentTypeID,
							UC_ManualEDP,
							UC_PartTimeEDP,
							UC_JobTitle,
							UC_Status,
							UC_StartDate,
							UC_EndDate,
							UC_SCP_ID,
							UC_CreatedBy,
							UC_CreatedDate)
					 SELECT @A7ID,
							SCP.EFT,
							SCP.CostCode,
							SCP.AccGroupID,
							SCP.AccGroup,
							SCP.PaymentTypeID,
							SCP.ManualEDP,
							SCP.PartTimeEDP,
							SCP.JobTitle,
							SCP.IsActive,
							SCP.StartDate,
							SCP.EndDate,
							SCP.SCP_ID,
							@UserId,
							SCP.CreatedDate
					   FROM StaffConfig_Processed SCP
					  WHERE SCP_ID = @SCPID

			END
		  ELSE 
		    BEGIN

			  IF EXISTS ( SELECT 1 
							FROM UserConfigs
						   WHERE UC_UserID = @A7ID 
							 AND UC_SCP_ID = @SCPID)
			    BEGIN

					 UPDATE UC
						SET	UC.UC_EndDate=SCP.EndDate,
							UC.UC_JobTitle=SCP.JobTitle,
							UC.UC_EFT=SCP.EFT,
							UC.UC_CostCode=SCP.CostCode,
							UC.UC_AccGroupID=SCP.AccGroupID,
							UC.UC_AccGroup=SCP.AccGroup,
							UC.UC_PartTimeEDP=SCP.PartTimeEDP,
							UC.UC_PaymentTypeID=SCP.PaymentTypeID,
							UC.UC_ManualEDP=SCP.ManualEDP,
							UC.UC_UpdatedDate=SCP.LastModDate,
							UC.UC_UpdatedBy=SCP.LastModBy
					   FROM StaffConfig_Processed AS SCP (nolock)
					  INNER JOIN UserDetails UD on SCP.StaffID = UD.UD_TeampayStaffID
					  INNER JOIN UserConfigs AS UC (nolock) ON UD.UD_UserID = UC.UC_UserID AND UC.UC_SCP_ID = SCP.SCP_ID
					  WHERE SCP.SCP_ID = @SCPID	

				END
			END
		END

		UPDATE StaffConfig_Processed
			SET COMMENTS = 'Approved' 
			WHERE SCP_ID = @SCPID
			 
		FETCH NEXT FROM @DataList INTO @SCPID, @StartDate
	END

	CLOSE @DataList
	
	DEALLOCATE @DataList

 END TRY
 
 BEGIN CATCH

    ROLLBACK TRANSACTION

	INSERT INTO ErrorLog
		VALUES ( ERROR_NUMBER(),
				 ERROR_STATE(),
				 ERROR_SEVERITY(),
				 ERROR_LINE(),
				 'usp_Sync_Missing_Records',
				 ERROR_MESSAGE(),
				 GETDATE(),
				 @UserId
				)
 
 END CATCH
END